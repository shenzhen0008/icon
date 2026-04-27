<?php

namespace App\Modules\Settlement\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductDailyReturn;
use App\Modules\Referral\Services\GrantReferralCommissionForSettlementService;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailySettlementService
{
    public function __construct(
        private readonly GrantReferralCommissionForSettlementService $grantReferralCommissionForSettlementService,
    ) {
    }

    public function settleByProductAndDate(int $productId, string $date): void
    {
        $product = Product::query()
            ->whereKey($productId)
            ->firstOrFail();

        $dailyReturn = $this->resolveOrCreateDailyReturn($product, $date);

        $this->settleOpenPositions($product->id, (float) $dailyReturn->rate, $date);
    }

    public function settleByProductCodeAndDate(string $productCode, string $date): void
    {
        $product = Product::query()
            ->where('code', $productCode)
            ->firstOrFail();

        $dailyReturn = $this->resolveOrCreateDailyReturn($product, $date);

        $this->settleOpenPositions($product->id, (float) $dailyReturn->rate, $date);
    }

    public function settleAllProductsByDate(string $date): void
    {
        Position::query()
            ->where('status', 'open')
            ->select('product_id')
            ->distinct()
            ->pluck('product_id')
            ->each(function (int $productId) use ($date): void {
                $this->settleByProductAndDate($productId, $date);
            });
    }

    private function settleOpenPositions(int $productId, float $rate, string $date): void
    {
        Position::query()
            ->where('product_id', $productId)
            ->where('status', 'open')
            ->orderBy('id')
            ->chunkById(200, function ($positions) use ($rate, $date): void {
                foreach ($positions as $position) {
                    $this->settleOnePosition($position, $rate, $date);
                }
            });
    }

    private function resolveOrCreateDailyReturn(Product $product, string $date): ProductDailyReturn
    {
        $existing = ProductDailyReturn::query()
            ->where('product_id', $product->id)
            ->whereDate('return_date', $date)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        if ($product->rate_min_percent === null || $product->rate_max_percent === null) {
            throw ValidationException::withMessages([
                'product_code' => '产品收益率区间未配置，无法自动结算。',
            ]);
        }

        $minRate = (int) round(((float) $product->rate_min_percent / 100) * 10000);
        $maxRate = (int) round(((float) $product->rate_max_percent / 100) * 10000);

        if ($maxRate < $minRate) {
            throw ValidationException::withMessages([
                'product_code' => '产品收益率区间配置错误，最小值不能大于最大值。',
            ]);
        }

        $hashSource = $product->code . '|' . $date . '|' . (config('app.key') ?? 'icon-market');
        $seed = abs((int) crc32($hashSource));
        $rateAsInt = $minRate + ($seed % max(1, ($maxRate - $minRate + 1)));
        $rate = $rateAsInt / 10000;

        try {
            return ProductDailyReturn::query()->create([
                'product_id' => $product->id,
                'return_date' => $date,
                'rate' => $rate,
            ]);
        } catch (QueryException) {
            return ProductDailyReturn::query()
                ->where('product_id', $product->id)
                ->whereDate('return_date', $date)
                ->firstOrFail();
        }
    }

    private function settleOnePosition(Position $position, float $rate, string $date): void
    {
        DB::transaction(function () use ($position, $rate, $date): void {
            $existing = DailySettlement::query()
                ->where('position_id', $position->id)
                ->whereDate('settlement_date', $date)
                ->exists();

            if ($existing) {
                return;
            }

            $profit = round((float) $position->principal * $rate, 2);

            $settlement = DailySettlement::query()->create([
                'user_id' => $position->user_id,
                'product_id' => $position->product_id,
                'position_id' => $position->id,
                'settlement_date' => $date,
                'rate' => $rate,
                'profit' => $profit,
            ]);

            $user = User::query()->lockForUpdate()->findOrFail($position->user_id);
            $beforeBalance = (float) $user->balance;
            $afterBalance = $beforeBalance + $profit;

            $user->balance = $afterBalance;
            $user->save();

            BalanceLedger::query()->create([
                'user_id' => $position->user_id,
                'type' => 'settlement_credit',
                'amount' => $profit,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'daily_settlement',
                'biz_ref_id' => $position->id . ':' . $date,
                'settlement_date' => $date,
                'occurred_at' => now(),
            ]);

            DB::afterCommit(function () use ($settlement): void {
                try {
                    $this->grantReferralCommissionForSettlementService->handle($settlement);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            });
        });
    }
}
