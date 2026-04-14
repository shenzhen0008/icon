<?php

namespace Tests\Feature\Reservation;

use App\Models\User;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitProductReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_product_reservation(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $this->actingAs($user)
            ->from('/products/'.$product->id)
            ->post('/products/'.$product->id.'/reservations', [
                'amount' => 3000,
            ])
            ->assertRedirect('/products/'.$product->id);

        $this->assertDatabaseHas('product_reservations', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount_usdt' => 3000,
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_submit_product_reservation(): void
    {
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $this->post('/products/'.$product->id.'/reservations', [
            'amount' => 2000,
        ])->assertRedirect('/login');
    }

    public function test_reservation_submission_rejects_invalid_input_or_non_reserve_product(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Direct Product',
            'code' => 'DRT',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'direct',
        ]);

        $this->actingAs($user)
            ->from('/products/'.$product->id)
            ->post('/products/'.$product->id.'/reservations', [
                'amount' => 0,
            ])
            ->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->actingAs($user)
            ->from('/products/'.$product->id)
            ->post('/products/'.$product->id.'/reservations', [
                'amount' => 1000,
            ])
            ->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['product']);
    }
}
