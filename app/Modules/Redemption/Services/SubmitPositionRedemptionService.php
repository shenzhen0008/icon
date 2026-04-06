<?php

namespace App\Modules\Redemption\Services;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitPositionRedemptionService
{
    public function submit(User $user, int $positionId): PositionRedemptionRequest
    {
        return DB::transaction(function () use ($user, $positionId): PositionRedemptionRequest {
            $position = Position::query()
                ->whereKey($positionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $position->user_id !== (int) $user->id) {
                abort(403);
            }

            if ($position->status !== 'open') {
                throw ValidationException::withMessages([
                    'position' => '当前持仓状态不支持发起赎回申请。',
                ]);
            }

            $pendingExists = PositionRedemptionRequest::query()
                ->where('position_id', $position->id)
                ->where('status', 'pending')
                ->exists();

            if ($pendingExists) {
                throw ValidationException::withMessages([
                    'position' => '该持仓已有待审核赎回申请。',
                ]);
            }

            $request = PositionRedemptionRequest::query()->create([
                'user_id' => $position->user_id,
                'position_id' => $position->id,
                'product_id' => $position->product_id,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            $position->status = 'redeeming';
            $position->save();

            return $request;
        });
    }
}
