<?php

namespace Tests\Feature\Home;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroPanelRecordPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_trade_and_income_record_pages(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Alpha Pool',
            'code' => 'ALPHA',
            'unit_price' => '1000.00',
            'is_active' => true,
            'sort' => 1,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => '500.00',
            'status' => 'open',
            'opened_at' => now()->subDay(),
        ]);

        $withdrawalRequest = WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'rejected',
            'submitted_at' => now()->subHours(10),
            'reviewed_at' => now()->subHours(9),
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'purchase_debit',
            'amount' => '-500.00',
            'before_balance' => '1000.00',
            'after_balance' => '500.00',
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
            'occurred_at' => now()->subDay(),
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'withdrawal_debit',
            'amount' => '-100.00',
            'before_balance' => '500.00',
            'after_balance' => '400.00',
            'biz_ref_type' => 'withdrawal_request',
            'biz_ref_id' => (string) $withdrawalRequest->id,
            'occurred_at' => now()->subHours(10),
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => now()->toDateString(),
            'rate' => '0.0200',
            'profit' => '10.00',
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'referral_commission_credit',
            'amount' => '15.10',
            'before_balance' => '400.00',
            'after_balance' => '415.10',
            'biz_ref_type' => 'referral_commission',
            'biz_ref_id' => 'settlement:99:level:1',
            'occurred_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->get('/home/hero-panel/trade-records')
            ->assertOk()
            ->assertSee('交易记录')
            ->assertSee('类型')
            ->assertSee('Alpha Pool')
            ->assertSee('提款至 T1234567890abcdef')
            ->assertSee('500.00');

        $this->actingAs($user)
            ->get('/home/hero-panel/income-records')
            ->assertOk()
            ->assertSee('收入记录')
            ->assertSee('Alpha Pool')
            ->assertSee('10.00')
            ->assertSee('推荐提成')
            ->assertSee('15.10');
    }

    public function test_demo_mode_pages_render_fixed_records(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/home/hero-panel/trade-records?mode=demo')
            ->assertOk()
            ->assertSee('DEMO')
            ->assertSee('BTC Grid Alpha')
            ->assertSee('1200.00');

        $this->actingAs($user)
            ->get('/home/hero-panel/income-records?mode=demo')
            ->assertOk()
            ->assertSee('DEMO')
            ->assertSee('ETH Trend Pulse')
            ->assertSee('1.88%');
    }

    public function test_guest_cannot_open_hero_panel_record_pages(): void
    {
        $this->get('/home/hero-panel/trade-records')
            ->assertRedirect('/login');

        $this->get('/home/hero-panel/income-records')
            ->assertRedirect('/login');
    }

    public function test_invalid_per_page_is_rejected_for_trade_records_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/home/hero-panel/trade-records?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_trade_records_page_localizes_fixed_ui_copy_for_english(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/home/hero-panel/trade-records?locale=en')
            ->assertOk()
            ->assertSee('Trade Records')
            ->assertSee('Type')
            ->assertSee('No trade records yet')
            ->assertSee('Back to Home');
    }

    public function test_income_records_page_localizes_fixed_ui_copy_for_english(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Alpha Pool',
            'code' => 'ALPHA',
            'unit_price' => '1000.00',
            'is_active' => true,
            'sort' => 1,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => '500.00',
            'status' => 'open',
            'opened_at' => now()->subDay(),
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => now()->toDateString(),
            'rate' => '0.0200',
            'profit' => '10.00',
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'referral_commission_credit',
            'amount' => '15.10',
            'before_balance' => '400.00',
            'after_balance' => '415.10',
            'biz_ref_type' => 'referral_commission',
            'biz_ref_id' => 'settlement:99:level:1',
            'occurred_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->get('/home/hero-panel/income-records?locale=en')
            ->assertOk()
            ->assertSee('Income Records')
            ->assertSee('Product')
            ->assertSee('Back to Home')
            ->assertSee('Referral Commission');
    }
}
