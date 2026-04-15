<?php

namespace Tests\Feature\Referral;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessReferralCommissionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_grants_two_level_commissions_for_positive_settlement(): void
    {
        [$levelTwoReferrer, $levelOneReferrer, $referredUser, $settlement] = $this->createReferralSettlement(100);

        $this->seedActiveSetting();

        $this->artisan('referral:commission-process')
            ->assertExitCode(0);

        $levelOneReferrer->refresh();
        $levelTwoReferrer->refresh();
        $referredUser->refresh();

        $this->assertSame('5.00', number_format((float) $levelOneReferrer->balance, 2, '.', ''));
        $this->assertSame('2.00', number_format((float) $levelTwoReferrer->balance, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $referredUser->balance, 2, '.', ''));

        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlement->id,
            'level' => 1,
            'referrer_id' => $levelOneReferrer->id,
            'referred_user_id' => $referredUser->id,
            'base_profit' => 100,
            'commission_rate' => '0.0500',
            'commission_amount' => 5,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlement->id,
            'level' => 2,
            'referrer_id' => $levelTwoReferrer->id,
            'referred_user_id' => $referredUser->id,
            'commission_rate' => '0.0200',
            'commission_amount' => 2,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $levelOneReferrer->id,
            'type' => 'referral_commission_credit',
            'amount' => 5,
            'biz_ref_type' => 'referral_commission',
            'biz_ref_id' => 'settlement:'.$settlement->id.':level:1',
        ]);
    }

    public function test_command_is_idempotent_for_repeated_runs(): void
    {
        [$levelTwoReferrer, $levelOneReferrer, , $settlement] = $this->createReferralSettlement(100);

        $this->seedActiveSetting();

        $this->artisan('referral:commission-process')->assertExitCode(0);
        $this->artisan('referral:commission-process')->assertExitCode(0);

        $levelOneReferrer->refresh();
        $levelTwoReferrer->refresh();

        $this->assertSame('5.00', number_format((float) $levelOneReferrer->balance, 2, '.', ''));
        $this->assertSame('2.00', number_format((float) $levelTwoReferrer->balance, 2, '.', ''));
        $this->assertDatabaseCount('referral_commission_records', 2);
        $this->assertDatabaseCount('balance_ledgers', 2);
        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlement->id,
            'level' => 1,
            'status' => 'success',
        ]);
    }

    public function test_non_positive_profit_does_not_create_commission_records(): void
    {
        $this->createReferralSettlement(0);
        $this->seedActiveSetting();

        $this->artisan('referral:commission-process')
            ->assertExitCode(0);

        $this->assertDatabaseCount('referral_commission_records', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_before_go_live_settlement_does_not_create_commission_records(): void
    {
        $this->createReferralSettlement(100, '2026-04-14');
        $this->seedActiveSetting();
        config(['referral.go_live_date' => '2026-04-15']);

        $this->artisan('referral:commission-process')
            ->assertExitCode(0);

        $this->assertDatabaseCount('referral_commission_records', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_missing_or_inactive_setting_skips_processing(): void
    {
        $this->createReferralSettlement(100);
        \DB::table('referral_commission_settings')->delete();

        $this->artisan('referral:commission-process')
            ->assertExitCode(0);

        $this->assertDatabaseCount('referral_commission_records', 0);

        $this->seedActiveSetting(false);

        $this->artisan('referral:commission-process')
            ->assertExitCode(0);

        $this->assertDatabaseCount('referral_commission_records', 0);
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: DailySettlement}
     */
    private function createReferralSettlement(float $profit, string $date = '2026-04-15'): array
    {
        $levelTwoReferrer = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'LEVEL2',
        ]);
        $levelOneReferrer = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'LEVEL1',
            'referrer_id' => $levelTwoReferrer->id,
        ]);
        $referredUser = User::factory()->create([
            'balance' => 0,
            'invite_code' => 'CHILD1',
            'referrer_id' => $levelOneReferrer->id,
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
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $settlement = DailySettlement::query()->create([
            'user_id' => $referredUser->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => $date,
            'rate' => 0.1,
            'profit' => $profit,
        ]);

        return [$levelTwoReferrer, $levelOneReferrer, $referredUser, $settlement];
    }

    private function seedActiveSetting(bool $active = true): void
    {
        \DB::table('referral_commission_settings')->updateOrInsert([
            'id' => 1,
        ], [
            'level_1_rate' => '0.0500',
            'level_2_rate' => '0.0200',
            'is_active' => $active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
