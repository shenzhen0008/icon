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
}
