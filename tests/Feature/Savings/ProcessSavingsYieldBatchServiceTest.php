<?php

namespace Tests\Feature\Savings;

use App\Models\User;
use App\Modules\Savings\Services\ProcessSavingsYieldBatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessSavingsYieldBatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_setting_grants_savings_interest_to_positive_balances(): void
    {
        $user = User::factory()->create(['balance' => 100]);
        User::factory()->create(['balance' => 0]);

        \DB::table('savings_yield_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'daily_rate' => '0.0100',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats = app(ProcessSavingsYieldBatchService::class)->handle('2026-04-24');

        $user->refresh();

        $this->assertSame('101.00', number_format((float) $user->balance, 2, '.', ''));
        $this->assertSame(1, $stats['scanned']);
        $this->assertSame(1, $stats['granted']);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'savings_interest_credit',
            'amount' => 1,
            'before_balance' => 100,
            'after_balance' => 101,
            'biz_ref_type' => 'savings_interest',
            'biz_ref_id' => '2026-04-24:'.$user->id,
        ]);
    }

    public function test_service_is_idempotent_for_same_date(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        \DB::table('savings_yield_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'daily_rate' => '0.0100',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(ProcessSavingsYieldBatchService::class);
        $service->handle('2026-04-24');
        $service->handle('2026-04-24');

        $user->refresh();

        $this->assertSame('101.00', number_format((float) $user->balance, 2, '.', ''));
        $this->assertDatabaseCount('balance_ledgers', 1);
    }

    public function test_inactive_setting_skips_processing(): void
    {
        User::factory()->create(['balance' => 100]);

        \DB::table('savings_yield_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'daily_rate' => '0.0100',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats = app(ProcessSavingsYieldBatchService::class)->handle('2026-04-24');

        $this->assertSame(0, $stats['scanned']);
        $this->assertSame(0, $stats['granted']);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }
}
