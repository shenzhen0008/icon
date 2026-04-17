<?php

namespace App\Modules\Withdrawal\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitWithdrawalRequestService
{
    public function submit(User $user, array $payload): WithdrawalRequest
    {
        return DB::transaction(function () use ($user, $payload): WithdrawalRequest {
            $freshUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (float) $payload['amount'];
            $beforeBalance = (float) $freshUser->balance;

            if ($beforeBalance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => '当前余额不足，无法提交提款申请。',
                ]);
            }

            $afterBalance = $beforeBalance - $amount;
            $submittedAt = now();

            $freshUser->balance = $afterBalance;
            $freshUser->save();

            $withdrawalRequest = WithdrawalRequest::query()->create([
                'user_id' => $freshUser->id,
                'asset_code' => (string) $payload['asset_code'],
                'network' => (string) $payload['network'],
                'destination_address' => (string) $payload['destination_address'],
                'meta_json' => $payload['meta_json'] ?? null,
                'amount' => $amount,
                'status' => 'pending',
                'submitted_at' => $submittedAt,
            ]);

            BalanceLedger::query()->create([
                'user_id' => $freshUser->id,
                'type' => 'withdrawal_debit',
                'amount' => -$amount,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'withdrawal_request',
                'biz_ref_id' => (string) $withdrawalRequest->id,
                'occurred_at' => $submittedAt,
            ]);

            return $withdrawalRequest;
        });
    }
}
