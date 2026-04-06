<?php

namespace Tests\Feature\Redemption;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Redemption\Services\ReviewPositionRedemptionService;
use App\Modules\Settlement\Services\DailySettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedemptionReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_redemption_returns_principal_and_closes_position(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);
        $admin = User::factory()->create();

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
            'status' => 'redeeming',
            'opened_at' => now(),
        ]);

        $requestId = (int) \DB::table('position_redemption_requests')->insertGetId([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'product_id' => $product->id,
            'status' => 'pending',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ReviewPositionRedemptionService::class)->approve($requestId, $admin->id, '通过');

        $this->assertDatabaseHas('position_redemption_requests', [
            'id' => $requestId,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'status' => 'redeemed',
        ]);

        $user->refresh();
        $this->assertSame('1100.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'redemption_credit',
            'amount' => 1000,
            'biz_ref_type' => 'redemption_request',
            'biz_ref_id' => (string) $requestId,
        ]);
    }

    public function test_reject_redemption_restores_open_and_no_retroactive_profit(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);
        $admin = User::factory()->create();

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
            'status' => 'redeeming',
            'opened_at' => now(),
        ]);

        $requestId = (int) \DB::table('position_redemption_requests')->insertGetId([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'product_id' => $product->id,
            'status' => 'pending',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-01');
        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-02');
        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-03');

        $this->assertDatabaseCount('daily_settlements', 0);

        app(ReviewPositionRedemptionService::class)->reject($requestId, $admin->id, '拒绝');

        $this->assertDatabaseHas('position_redemption_requests', [
            'id' => $requestId,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'status' => 'open',
        ]);

        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-04');

        $this->assertDatabaseHas('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-04',
        ]);

        $this->assertDatabaseMissing('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-01',
        ]);
        $this->assertDatabaseMissing('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-02',
        ]);
        $this->assertDatabaseMissing('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-03',
        ]);
    }
}
