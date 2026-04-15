<?php

namespace App\Modules\OnchainRecharge\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\OnchainRecharge\Support\OnchainRechargeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewOnchainRechargeRequestService
{
    public function markProcessed(int $requestId, int $reviewerId, null|string $reviewNote = null): void
    {
        DB::transaction(function () use ($requestId, $reviewerId, $reviewNote): void {
            $request = RechargePaymentRequest::query()
                ->whereKey($requestId)
                ->where('channel', OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== OnchainRechargeStatus::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => '该链上充值申请已处理，不能重复处理。',
                ]);
            }

            if (! is_string($request->tx_hash) || trim($request->tx_hash) === '' || ! is_string($request->to_address) || trim($request->to_address) === '') {
                throw ValidationException::withMessages([
                    'tx_hash' => '该链上充值申请缺少关键交易信息，无法入账。',
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

            $request->status = OnchainRechargeStatus::STATUS_PROCESSED;
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
                ->where('channel', OnchainRechargeStatus::CHANNEL_ONCHAIN_WALLET)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== OnchainRechargeStatus::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => '该链上充值申请已处理，不能重复处理。',
                ]);
            }

            $request->status = OnchainRechargeStatus::STATUS_REJECTED;
            $request->reviewed_by = $reviewerId;
            $request->reviewed_at = now();
            $request->review_note = $reviewNote;
            $request->save();
        });
    }
}
