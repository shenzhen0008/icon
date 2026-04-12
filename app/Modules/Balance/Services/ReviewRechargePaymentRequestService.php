<?php

namespace App\Modules\Balance\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Balance\Models\RechargePaymentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewRechargePaymentRequestService
{
    public function markProcessed(int $requestId, int $reviewerId, null|string $reviewNote = null): void
    {
        DB::transaction(function () use ($requestId, $reviewerId, $reviewNote): void {
            $request = RechargePaymentRequest::query()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => '该充值申请已处理，不能重复处理。',
                ]);
            }

            $user = User::query()
                ->whereKey($request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $creditedAmount = (float) $request->payment_amount;
            $beforeBalance = (float) $user->balance;
            $afterBalance = $beforeBalance + $creditedAmount;
            $reviewedAt = now();

            $user->balance = $afterBalance;
            $user->save();

            BalanceLedger::query()->create([
                'user_id' => $user->id,
                'type' => 'recharge_credit',
                'amount' => $creditedAmount,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'recharge_payment_request',
                'biz_ref_id' => (string) $request->id,
                'occurred_at' => $reviewedAt,
            ]);

            $request->status = 'processed';
            $request->reviewed_by = $reviewerId;
            $request->reviewed_at = $reviewedAt;
            $request->review_note = $reviewNote;
            $request->save();
        });
    }

    public function reject(int $requestId, int $reviewerId, null|string $reviewNote = null): void
    {
        DB::transaction(function () use ($requestId, $reviewerId, $reviewNote): void {
            $request = RechargePaymentRequest::query()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => '该充值申请已处理，不能重复处理。',
                ]);
            }

            $request->status = 'rejected';
            $request->reviewed_by = $reviewerId;
            $request->reviewed_at = now();
            $request->review_note = $reviewNote;
            $request->save();
        });
    }
}
