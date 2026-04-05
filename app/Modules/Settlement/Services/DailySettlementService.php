<?php

namespace App\Modules\Settlement\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\ProductDailyReturn;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Support\Facades\DB;

class DailySettlementService
{
    public function settleByProductAndDate(int $productId, string $date): void
    {
        $dailyReturn = ProductDailyReturn::query()
            ->where('product_id', $productId)
            ->whereDate('return_date', $date)
            ->firstOrFail();

        Position::query()
            ->where('product_id', $productId)
            ->where('status', 'open')
            ->orderBy('id')
            ->chunkById(200, function ($positions) use ($dailyReturn, $date): void {
                foreach ($positions as $position) {
                    $this->settleOnePosition($position, (float) $dailyReturn->rate, $date);
                }
            });
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

            DailySettlement::query()->create([
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
                'occurred_at' => now(),
            ]);
        });
    }
}
