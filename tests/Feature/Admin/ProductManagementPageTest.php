<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_product_management_pages_in_local_environment(): void
    {
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('产品管理');
        $this->get('/admin/products')
            ->assertOk()
            ->assertSee('手动触发当日结算');
        $this->get('/admin/products/create')
            ->assertOk()
            ->assertDontSee('按钮文案')
            ->assertDontSee('产品介绍')
            ->assertSee('多语言介绍')
            ->assertSee('语言')
            ->assertSee('介绍文案');
        $this->get('/admin/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertDontSee('按钮文案')
            ->assertDontSee('产品介绍')
            ->assertSee('多语言介绍');
    }

    public function test_manual_settle_today_action_also_processes_savings_yield(): void
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

        Livewire::test(ListProducts::class)
            ->callAction('settleTodayAll');

        $user->refresh();
        $date = now((string) config('settlement.timezone', 'Asia/Shanghai'))->toDateString();

        $this->assertSame('101.00', number_format((float) $user->balance, 2, '.', ''));
        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'savings_interest_credit',
            'amount' => 1,
            'biz_ref_type' => 'savings_interest',
            'biz_ref_id' => $date.':'.$user->id,
        ]);
    }

    public function test_manual_settle_today_action_also_processes_referral_commission_batch(): void
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

        Livewire::test(ListProducts::class)
            ->callAction('settleTodayAll');

        $referrer->refresh();

        $this->assertSame('5.00', number_format((float) $referrer->balance, 2, '.', ''));
        $this->assertDatabaseHas('referral_commission_records', [
            'settlement_id' => $settlement->id,
            'level' => 1,
            'status' => 'success',
            'commission_amount' => 5,
        ]);
        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $referrer->id,
            'type' => 'referral_commission_credit',
            'amount' => 5,
            'biz_ref_type' => 'referral_commission',
            'biz_ref_id' => 'settlement:'.$settlement->id.':level:1',
        ]);
    }
}
