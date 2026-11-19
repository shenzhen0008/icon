<?php

namespace App\Modules\Balance\Services;

use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder;
use App\Modules\Balance\Models\BalanceLedger;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAdminReservationFundOrderService
{
    public const AMOUNT_RULE = 'regex:/\\A[0-9]{1,14}(?:\\.[0-9]{1,2})?\\z/';

    public function handle(User $actor, int $userId, array $input): AdminReservationFundOrder
    {
        Gate::forUser($actor)->authorize('access-admin');

        $data = Validator::make($input, [
            'type' => ['sometimes', 'required', Rule::in([AdminReservationFundOrder::DEBIT])],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['bail', 'required', self::AMOUNT_RULE, 'numeric', 'min:0.01'],
        ])->validate();

        $amount = BigDecimal::of((string) $data['amount'])->toScale(2);

        return DB::transaction(function () use ($userId, $data, $amount): AdminReservationFundOrder {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $before = BigDecimal::of($user->balance);
            $after = BigDecimal::max($before->minus($amount), '0.00');

            $order = new AdminReservationFundOrder([
                'user_id' => $user->id,
                'type' => AdminReservationFundOrder::DEBIT,
                'name' => $data['name'],
                'amount' => (string) $amount,
            ]);
            $order->save();

            $user->update(['balance' => (string) $after]);

            BalanceLedger::query()->create([
                'user_id' => $user->id,
                'type' => $order->type,
                'amount' => $order->signedAmount(),
                'before_balance' => (string) $before,
                'after_balance' => (string) $after,
                'biz_ref_type' => AdminReservationFundOrder::REFERENCE_TYPE,
                'biz_ref_id' => (string) $order->id,
                'occurred_at' => $order->created_at,
            ]);

            return $order;
        });
    }
}
