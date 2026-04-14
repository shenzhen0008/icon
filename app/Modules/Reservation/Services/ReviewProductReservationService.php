<?php

namespace App\Modules\Reservation\Services;

use App\Modules\Position\Exceptions\InsufficientBalanceException;
use App\Modules\Position\Services\PurchasePositionService;
use App\Modules\Reservation\Models\ProductReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewProductReservationService
{
    public function __construct(private readonly PurchasePositionService $purchasePositionService)
    {
    }

    public function approve(int $reservationId, ?int $reviewerId, ?string $reviewNote = null): ProductReservation
    {
        return $this->approveAndConvert($reservationId, $reviewerId, $reviewNote);
    }

    public function reject(int $reservationId, ?int $reviewerId, ?string $reviewNote = null): ProductReservation
    {
        return DB::transaction(function () use ($reservationId, $reviewerId, $reviewNote): ProductReservation {
            $reservation = ProductReservation::query()
                ->lockForUpdate()
                ->findOrFail($reservationId);

            if ($reservation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => '仅待审核订单可执行拒绝。',
                ]);
            }

            $reservation->fill([
                'status' => 'rejected',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_note' => $reviewNote,
            ]);
            $reservation->save();

            return $reservation;
        });
    }

    /**
     * @throws InsufficientBalanceException
     */
    public function approveAndConvert(int $reservationId, ?int $reviewerId, ?string $reviewNote = null): ProductReservation
    {
        return DB::transaction(function () use ($reservationId, $reviewerId, $reviewNote): ProductReservation {
            $reservation = ProductReservation::query()
                ->with('user:id,balance')
                ->lockForUpdate()
                ->findOrFail($reservationId);

            if (! in_array($reservation->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages([
                    'status' => '仅待审核或已通过订单可转正式订单。',
                ]);
            }

            if ($reservation->converted_position_id !== null || $reservation->status === 'converted') {
                throw ValidationException::withMessages([
                    'status' => '该预订订单已完成转化。',
                ]);
            }

            $position = $this->purchasePositionService->purchase(
                $reservation->user,
                (int) $reservation->product_id,
                (float) $reservation->amount_usdt,
                false,
            );

            $reservation->fill([
                'status' => 'converted',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'approved_at' => $reservation->approved_at ?? now(),
                'converted_at' => now(),
                'converted_position_id' => $position->id,
                'review_note' => $reviewNote,
            ]);
            $reservation->save();

            return $reservation;
        });
    }
}
