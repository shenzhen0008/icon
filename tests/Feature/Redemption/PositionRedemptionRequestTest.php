<?php

namespace Tests\Feature\Redemption;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Services\DailySettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionRedemptionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_redemption_request_and_position_stops_settlement_immediately(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'rate_min_percent' => 1.00,
            'rate_max_percent' => 1.00,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/me/positions/'.$position->id.'/redemption-requests');

        $response->assertRedirect();

        $this->assertDatabaseHas('position_redemption_requests', [
            'user_id' => $user->id,
            'position_id' => $position->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'status' => 'redeeming',
        ]);

        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-06');

        $this->assertDatabaseMissing('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-06',
        ]);
    }

    public function test_user_cannot_submit_redemption_request_for_others_position(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

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

        $this->actingAs($other)
            ->post('/me/positions/'.$position->id.'/redemption-requests')
            ->assertForbidden();
    }

    public function test_user_cannot_submit_redemption_request_when_position_is_not_open(): void
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
            'status' => 'redeemed',
            'opened_at' => now(),
        ]);

        $this->from('/me/positions/'.$position->id)
            ->actingAs($user)
            ->post('/me/positions/'.$position->id.'/redemption-requests')
            ->assertRedirect('/me/positions/'.$position->id)
            ->assertSessionHasErrors(['position']);
    }
}
