<?php

namespace App\Modules\Position\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasePositionService
{
    public function purchase(User $user, int $productId, int $shares): Position
    {
        return DB::transaction(function () use ($user, $productId, $shares): Position {
            /** @var Product $product */
            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->firstOrFail();

            $amount = (float) $product->unit_price * $shares;

            $openPositions = Position::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->get(['id', 'principal']);

            $currentPurchaseCount = $openPositions->count();
            $currentPrincipal = (float) $openPositions->sum(static fn (Position $position): float => (float) $position->principal);

            if ($product->purchase_limit !== null && $currentPurchaseCount >= (int) $product->purchase_limit) {
                throw ValidationException::withMessages([
                    'shares' => '该产品购买次数已达上限。',
                ]);
            }

            if ($product->limit_min_usdt !== null && $amount < (float) $product->limit_min_usdt) {
                throw ValidationException::withMessages([
                    'shares' => '购买金额低于产品最低限额。',
                ]);
            }

            $nextPrincipal = $currentPrincipal + $amount;

            if ($product->limit_max_usdt !== null && $nextPrincipal > (float) $product->limit_max_usdt) {
                throw ValidationException::withMessages([
                    'shares' => '购买后累计金额超过产品上限。',
                ]);
            }

            $freshUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ((float) $freshUser->balance < $amount) {
                throw ValidationException::withMessages([
                    'shares' => '当前余额不足，无法完成购买。',
                ]);
            }

            $beforeBalance = (float) $freshUser->balance;
            $afterBalance = $beforeBalance - $amount;

            $freshUser->balance = $afterBalance;
            $freshUser->save();

            $position = Position::query()->create([
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
}
