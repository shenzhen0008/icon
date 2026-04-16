<?php

namespace Tests\Feature\Settlement;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductDailyReturn;
use App\Modules\Settlement\Services\DailySettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailySettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_credits_profit_by_product_daily_rate(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
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

        ProductDailyReturn::query()->create([
            'product_id' => $product->id,
            'return_date' => '2026-04-04',
            'rate' => 0.1,
        ]);

        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-04');

        $user->refresh();
        $this->assertSame('100.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('daily_settlements', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-04',
            'profit' => 100,
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'settlement_credit',
            'amount' => 100,
            'before_balance' => 0,
            'after_balance' => 100,
            'biz_ref_type' => 'daily_settlement',
            'biz_ref_id' => (string) $position->id . ':2026-04-04',
        ]);
    }

    public function test_settlement_is_idempotent_for_same_position_and_date(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
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

        ProductDailyReturn::query()->create([
            'product_id' => $product->id,
            'return_date' => '2026-04-04',
            'rate' => 0.1,
        ]);

        $service = app(DailySettlementService::class);
        $service->settleByProductAndDate($product->id, '2026-04-04');
        $service->settleByProductAndDate($product->id, '2026-04-04');

        $user->refresh();
        $this->assertSame('100.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseCount('daily_settlements', 1);
        $this->assertDatabaseCount('balance_ledgers', 1);
    }

    public function test_settlement_by_product_code_generates_daily_rate_and_settles_each_order(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'rate_min_percent' => 1.15,
            'rate_max_percent' => 2.22,
        ]);

        $positionA = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $positionB = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 9000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $service = app(DailySettlementService::class);
        $service->settleByProductCodeAndDate('MAMM', '2026-04-06');

        $dailyReturn = ProductDailyReturn::query()
            ->where('product_id', $product->id)
            ->whereDate('return_date', '2026-04-06')
            ->firstOrFail();

        $rate = (float) $dailyReturn->rate;
        $this->assertGreaterThanOrEqual(0.0115, $rate);
        $this->assertLessThanOrEqual(0.0222, $rate);

        $profitA = round(1000 * $rate, 2);
        $profitB = round(9000 * $rate, 2);

        $this->assertDatabaseHas('daily_settlements', [
            'position_id' => $positionA->id,
            'settlement_date' => '2026-04-06',
            'rate' => $rate,
            'profit' => $profitA,
        ]);

        $this->assertDatabaseHas('daily_settlements', [
            'position_id' => $positionB->id,
            'settlement_date' => '2026-04-06',
            'rate' => $rate,
            'profit' => $profitB,
        ]);

        $user->refresh();
        $this->assertSame(number_format($profitA + $profitB, 2, '.', ''), number_format((float) $user->balance, 2, '.', ''));
    }

    public function test_settlement_by_product_code_is_idempotent_and_keeps_one_daily_rate_record(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'rate_min_percent' => 1.15,
            'rate_max_percent' => 2.22,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $service = app(DailySettlementService::class);
        $service->settleByProductCodeAndDate('MAMM', '2026-04-06');
        $service->settleByProductCodeAndDate('MAMM', '2026-04-06');

        $this->assertDatabaseCount('product_daily_returns', 1);
        $this->assertDatabaseCount('daily_settlements', 1);
        $this->assertDatabaseCount('balance_ledgers', 1);
    }

    public function test_settlement_all_products_by_date_settles_all_open_positions(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $productA = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'rate_min_percent' => 1.10,
            'rate_max_percent' => 1.20,
        ]);

        $productB = Product::query()->create([
            'name' => 'Alpha Pool',
            'code' => 'ALPHA',
            'unit_price' => 1000,
            'is_active' => false,
            'rate_min_percent' => 1.30,
            'rate_max_percent' => 1.40,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $productB->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        app(DailySettlementService::class)->settleAllProductsByDate('2026-04-06');

        $this->assertDatabaseCount('product_daily_returns', 2);
        $this->assertDatabaseCount('daily_settlements', 2);
        $this->assertDatabaseCount('balance_ledgers', 2);
    }

    public function test_settlement_triggers_referral_commission_after_commit(): void
    {
        $referrer = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'REF001',
        ]);

        $user = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'USER01',
            'referrer_id' => $referrer->id,
        ]);

        \DB::table('referral_commission_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'level_1_rate' => '0.0500',
            'level_2_rate' => '0.0200',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::query()->create([
            'name' => 'Referral Trigger Product',
            'code' => 'RTP',
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

        ProductDailyReturn::query()->create([
            'product_id' => $product->id,
            'return_date' => '2026-04-16',
            'rate' => 0.1,
        ]);

        app(DailySettlementService::class)->settleByProductAndDate($product->id, '2026-04-16');

        $referrer->refresh();
        $this->assertSame('5.00', number_format((float) $referrer->balance, 2, '.', ''));

        $settlementId = \DB::table('daily_settlements')
            ->where('position_id', $position->id)
            ->value('id');

        $this->assertNotNull($settlementId);

        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlementId,
            'level' => 1,
            'referrer_id' => $referrer->id,
            'referred_user_id' => $user->id,
            'commission_amount' => 5,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $referrer->id,
            'type' => 'referral_commission_credit',
            'amount' => 5,
            'biz_ref_type' => 'referral_commission',
            'biz_ref_id' => 'settlement:'.$settlementId.':level:1',
        ]);
    }
}
