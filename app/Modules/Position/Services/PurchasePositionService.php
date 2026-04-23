<?php

namespace App\Modules\Position\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Exceptions\InsufficientBalanceException;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasePositionService
{
    public function purchase(User $user, int $productId, float $amount, bool $enforceDirectMode = true): Position
    {
        return DB::transaction(function () use ($user, $productId, $amount, $enforceDirectMode): Position {
            /** @var Product $product */
            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->firstOrFail();

            if ($enforceDirectMode && $product->trade_mode === 'reserve') {
                throw ValidationException::withMessages([
                    'amount' => '预订商品不支持直接购买，请先提交预订申请。',
                ]);
            }

            $purchasedCount = Position::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->count();

            if ($product->purchase_limit_count !== null && $purchasedCount >= (int) $product->purchase_limit_count) {
                throw ValidationException::withMessages([
                    'amount' => '已达到该产品限购次数。',
                ]);
            }

            $openPositions = Position::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->get(['id', 'principal']);

            $currentPrincipal = (float) $openPositions->sum(static fn (Position $position): float => (float) $position->principal);

            if ($product->limit_min_usdt !== null && $amount < (float) $product->limit_min_usdt) {
                throw ValidationException::withMessages([
                    'amount' => '购买金额低于产品最低限额。',
                ]);
            }

            $nextPrincipal = $currentPrincipal + $amount;

            if ($product->limit_max_usdt !== null && $nextPrincipal > (float) $product->limit_max_usdt) {
                throw ValidationException::withMessages([
                    'amount' => '购买后累计金额超过产品上限。',
                ]);
            }

            $freshUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ((float) $freshUser->balance < $amount) {
                throw new InsufficientBalanceException('当前余额不足，无法完成购买。');
            }

            $beforeBalance = (float) $freshUser->balance;
            $afterBalance = $beforeBalance - $amount;

            $freshUser->balance = $afterBalance;
            $freshUser->save();

            $position = Position::query()->create([
                'order_no' => $this->buildOrderNo(),
                'user_id' => $freshUser->id,
                'product_id' => $product->id,
                'principal' => $amount,
                'status' => 'open',
                'opened_at' => now(),
            ]);

            BalanceLedger::query()->create([
                'user_id' => $freshUser->id,
                'type' => 'purchase_debit',
                'amount' => -$amount,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'position',
                'biz_ref_id' => (string) $position->id,
                'occurred_at' => now(),
            ]);

            return $position;
        });
    }

    private function buildOrderNo(): string
    {
        $maxAttempts = 20;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $orderNo = (string) random_int(100000, 999999);

            $exists = Position::query()
                ->where('order_no', $orderNo)
                ->exists();

            if (! $exists) {
                return $orderNo;
            }
        }

        throw ValidationException::withMessages([
            'amount' => '订单号生成失败，请稍后重试。',
        ]);
    }
}
