<?php

namespace App\Modules\Redemption\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewPositionRedemptionService
{
    public function approve(int $requestId, int $reviewerId, null|string $remark = null): void
    {
        DB::transaction(function () use ($requestId, $reviewerId, $remark): void {
            $request = PositionRedemptionRequest::query()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => '该赎回申请已处理，不能重复审批。',
                ]);
            }

            $position = Position::query()
                ->whereKey($request->position_id)
                ->lockForUpdate()
                ->firstOrFail();

            $owner = User::query()->lockForUpdate()->findOrFail($position->user_id);

            $beforeBalance = (float) $owner->balance;
            $credit = (float) $position->principal;
            $afterBalance = $beforeBalance + $credit;

            $owner->balance = $afterBalance;
            $owner->save();

            BalanceLedger::query()->create([
                'user_id' => $owner->id,
                'type' => 'redemption_credit',
                'amount' => $credit,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'redemption_request',
                'biz_ref_id' => (string) $request->id,
                'occurred_at' => now(),
            ]);

            $position->status = 'redeemed';
            $position->closed_at = now();
            $position->save();

            $request->status = 'approved';
            $request->reviewed_by = $reviewerId;
            $request->reviewed_at = now();
            $request->review_remark = $remark;
            $request->save();
        });
    }

    public function reject(int $requestId, int $reviewerId, null|string $remark = null): void
    {
        DB::transaction(function () use ($requestId, $reviewerId, $remark): void {
            $request = PositionRedemptionRequest::query()
                ->whereKey($requestId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => '该赎回申请已处理，不能重复审批。',
                ]);
            }

            $position = Position::query()
                ->whereKey($request->position_id)
                ->lockForUpdate()
                ->firstOrFail();

            $position->status = 'open';
            $position->save();

            $request->status = 'rejected';
            $request->reviewed_by = $reviewerId;
            $request->reviewed_at = now();
            $request->review_remark = $remark;
            $request->save();
        });
    }
}
