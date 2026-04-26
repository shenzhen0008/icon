<?php

namespace Tests\Feature\Settlement;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductDailyReturn;
use App\Modules\Settlement\Models\DailySettlement;
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

    public function test_command_processes_savings_yield_when_active(): void
    {
        $user = User::factory()->create([
            'balance' => 100,
        ]);

        \DB::table('savings_yield_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'daily_rate' => '0.0100',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('settlement:daily', ['--date' => '2026-04-16'])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertSame('101.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'savings_interest_credit',
            'amount' => 1,
            'biz_ref_type' => 'savings_interest',
            'biz_ref_id' => '2026-04-16:'.$user->id,
        ]);
    }

    public function test_command_also_processes_referral_commission_batch(): void
    {
        config(['referral.go_live_date' => '2026-04-15']);

        $referrer = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'REF001',
        ]);
        $referredUser = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'USR001',
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
            'name' => 'Referral Product',
            'code' => 'REFP',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'user_id' => $referredUser->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'closed',
            'opened_at' => now()->subDay(),
            'closed_at' => now(),
        ]);

        $settlement = DailySettlement::query()->create([
            'user_id' => $referredUser->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-16',
            'rate' => '0.1000',
            'profit' => 100,
        ]);

        $this->artisan('settlement:daily', ['--date' => '2026-04-16'])
            ->assertExitCode(0);

        $referrer->refresh();
        $this->assertSame('5.00', number_format((float) $referrer->balance, 2, '.', ''));
        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlement->id,
            'level' => 1,
            'status' => 'success',
            'commission_amount' => 5,
        ]);
    }

    public function test_command_auto_returns_principal_for_matured_position(): void
    {
        $user = User::factory()->create([
            'balance' => 0,
        ]);

        $product = Product::query()->create([
            'name' => 'Cycle Product',
            'code' => 'CYCLE-P',
            'unit_price' => 1000,
            'is_active' => true,
            'cycle_days' => 2,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => '2026-04-01 08:00:00',
        ]);

        ProductDailyReturn::query()->create([
            'product_id' => $product->id,
            'return_date' => '2026-04-03',
            'rate' => 0.1,
        ]);

        $this->artisan('settlement:daily', ['--date' => '2026-04-03'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('daily_settlements', [
            'position_id' => $position->id,
            'settlement_date' => '2026-04-03',
            'profit' => 100,
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'principal_return_credit',
            'amount' => 1000,
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
        ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'status' => 'closed',
        ]);

        $user->refresh();
        $this->assertSame('1100.00', number_format((float) $user->balance, 2, '.', ''));
    }
}
