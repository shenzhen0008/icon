<?php

namespace Tests\Feature\Position;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionOrderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_position_order_page_with_daily_profit_list(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-05',
            'rate' => 0.0183,
            'profit' => 18.3,
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-06',
            'rate' => 0.02,
            'profit' => 20,
        ]);

        $response = $this->actingAs($user)->get('/me/positions/'.$position->id);

        $response->assertOk();
        $response->assertSee('订单详情');
        $response->assertSee('Mobile AMM');
        $response->assertSee('每日收益');
        $response->assertSeeInOrder(['2026-04-06', '2026-04-05']);
        $response->assertSee('2.00%');
        $response->assertSee('1.83%');
        $response->assertSee('20.00');
        $response->assertSee('18.30');
        $response->assertSee('申请赎回');
        $response->assertSee('产品赎回后产品价值将会回到账户余额不会得到收益');
    }

    public function test_user_cannot_view_others_position_order_page(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->actingAs($anotherUser)
            ->get('/me/positions/'.$position->id)
            ->assertForbidden();
    }

    public function test_position_order_page_returns_404_for_invalid_position_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/me/positions/999999')
            ->assertNotFound();
    }
}
