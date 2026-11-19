<?php

namespace App\Modules\Balance\Services;

use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReleaseAdminReservationFundOrderService
{
    private const MAX_BALANCE = '99999999999999.99';

    public function handle(User $actor, int $userId, int $orderId): void
    {
        Gate::forUser($actor)->authorize('access-admin');

        DB::transaction(function () use ($userId, $orderId): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $order = AdminReservationFundOrder::query()
                ->where('user_id', $userId)
                ->where('type', AdminReservationFundOrder::DEBIT)
                ->find($orderId);

            if ($order === null || $order->releaseLedger()->exists()) {
                throw ValidationException::withMessages(['order_id' => '请选择该用户尚未解冻的扣款订单。']);
            }

            $before = BigDecimal::of($user->balance);
            $after = $before->plus($order->amount);
            if ($after->isGreaterThan(self::MAX_BALANCE)) {
                throw ValidationException::withMessages(['order_id' => '解冻后的余额超过允许上限。']);
            }

            $user->update(['balance' => (string) $after]);
            $order->releaseLedger()->create([
                'user_id' => $user->id,
                'type' => AdminReservationFundOrder::RELEASE,
                'amount' => $order->amount,
                'before_balance' => (string) $before,
                'after_balance' => (string) $after,
                'biz_ref_type' => AdminReservationFundOrder::REFERENCE_TYPE,
                'occurred_at' => now(),
            ]);
        });
    }
}
