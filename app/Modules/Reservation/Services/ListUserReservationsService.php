<?php

namespace App\Modules\Reservation\Services;

use App\Models\User;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\Reservation\Models\ProductReservation;

class ListUserReservationsService
{
    public function __construct(private readonly ProductTranslationService $productTranslationService)
    {
    }

    /**
     * @return array<int, array{
     *     id:int,
     *     product_name:string,
     *     amount_usdt:string,
     *     status:string,
     *     created_at:string
     * }>
     */
    public function handle(User $user): array
    {
        return ProductReservation::query()
            ->with(['product:id,name', 'product.translations'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'converted')
            ->latest('id')
            ->get()
            ->map(fn (ProductReservation $reservation): array => [
                'id' => $reservation->id,
                'product_name' => $this->productTranslationService->resolveName($reservation->product, emptyFallback: '--'),
                'amount_usdt' => number_format((float) $reservation->amount_usdt, 2, '.', ''),
                'status' => $reservation->status,
                'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s') ?? '--',
            ])
            ->all();
    }
}
