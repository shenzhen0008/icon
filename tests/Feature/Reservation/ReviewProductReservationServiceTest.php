<?php

namespace Tests\Feature\Reservation;

use App\Models\User;
use App\Modules\Position\Exceptions\InsufficientBalanceException;
use App\Modules\Product\Models\Product;
use App\Modules\Reservation\Models\ProductReservation;
use App\Modules\Reservation\Services\ReviewProductReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewProductReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_converts_reservation_to_real_order_and_debits_balance(): void
    {
        $user = User::factory()->create([
            'balance' => 6000,
        ]);
        $admin = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $reservation = ProductReservation::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount_usdt' => 2000,
            'status' => 'pending',
        ]);

        app(ReviewProductReservationService::class)->approve(
            (int) $reservation->id,
            (int) $admin->id,
            '通过并转正式订单',
        );

        $reservation->refresh();
        $user->refresh();

        $this->assertSame('converted', $reservation->status);
        $this->assertNotNull($reservation->converted_position_id);
        $this->assertSame('4000.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('positions', [
            'id' => $reservation->converted_position_id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 2000,
            'status' => 'open',
        ]);
    }

    public function test_approve_throws_when_balance_insufficient_and_keeps_pending(): void
    {
        $user = User::factory()->create([
            'balance' => 500,
        ]);
        $admin = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $reservation = ProductReservation::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount_usdt' => 1000,
            'status' => 'pending',
        ]);

        $this->expectException(InsufficientBalanceException::class);

        try {
            app(ReviewProductReservationService::class)->approve(
                (int) $reservation->id,
                (int) $admin->id,
                '通过并转正式订单',
            );
        } finally {
            $reservation->refresh();
            $this->assertSame('pending', $reservation->status);
            $this->assertNull($reservation->converted_position_id);
            $this->assertDatabaseCount('positions', 0);
        }
    }
}
