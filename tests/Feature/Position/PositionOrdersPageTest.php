<?php

namespace Tests\Feature\Position;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionOrdersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_orders_page(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/me/orders')
            ->assertOk()
            ->assertSee('订单')
            ->assertSee('持仓产品')
            ->assertSee('Mobile AMM')
            ->assertSee('查看订单');
    }

    public function test_guest_is_redirected_from_orders_page(): void
    {
        $this->get('/me/orders')
            ->assertRedirect('/login');
    }

    public function test_orders_page_shows_open_and_redeeming_positions_but_hides_redeemed_positions(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 2000,
            'status' => 'redeeming',
            'opened_at' => now(),
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 3000,
            'status' => 'redeemed',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/me/orders')
            ->assertOk()
            ->assertSee('持有中')
            ->assertSee('赎回中')
            ->assertDontSee('已赎回');
    }
}
