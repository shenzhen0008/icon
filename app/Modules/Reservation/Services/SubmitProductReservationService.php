<?php

namespace App\Modules\Reservation\Services;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Reservation\Models\ProductReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitProductReservationService
{
    public function submit(User $user, int $productId, float $amount): ProductReservation
    {
        return DB::transaction(function () use ($user, $productId, $amount): ProductReservation {
            /** @var Product $product */
            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->firstOrFail();

            if ($product->trade_mode !== 'reserve') {
                throw ValidationException::withMessages([
                    'product' => '该商品当前不支持预订。',
                ]);
            }

            return ProductReservation::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount_usdt' => $amount,
                'status' => 'pending',
            ]);
        });
    }
}
