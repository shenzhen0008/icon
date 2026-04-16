<?php

namespace Tests\Feature\Settlement;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductDailyReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessDailySettlementCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_settles_for_given_date(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Command Product',
            'code' => 'CMD-P',
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

        $this->artisan('settlement:daily', ['--date' => '2026-04-16'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-16',
            'profit' => 100,
        ]);
    }

    public function test_command_is_idempotent_for_same_date(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Command Product',
            'code' => 'CMD-P',
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
            'return_date' => '2026-04-16',
            'rate' => 0.1,
        ]);

        $this->artisan('settlement:daily', ['--date' => '2026-04-16'])->assertExitCode(0);
        $this->artisan('settlement:daily', ['--date' => '2026-04-16'])->assertExitCode(0);

        $this->assertDatabaseCount('daily_settlements', 1);
        $this->assertDatabaseCount('balance_ledgers', 1);
    }
}

