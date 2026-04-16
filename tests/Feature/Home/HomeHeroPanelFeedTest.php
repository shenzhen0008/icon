<?php

namespace Tests\Feature\Home;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\Withdrawal\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroPanelFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_mode_returns_user_balance_profit_and_records(): void
    {
        Carbon::setTestNow('2026-04-16 12:00:00');

        $user = User::factory()->create([
            'balance' => '1200.50',
        ]);

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MOB-AMM',
            'unit_price' => '1000.00',
            'is_active' => true,
            'sort' => 1,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => '500.00',
            'status' => 'open',
            'opened_at' => '2026-04-15 10:00:00',
        ]);

        $withdrawalRequest = WithdrawalRequest::query()->create([
            'user_id' => $user->id,
            'asset_code' => 'USDT',
            'network' => 'TRC20',
            'destination_address' => 'T1234567890abcdef',
            'amount' => '100.00',
            'status' => 'rejected',
            'submitted_at' => '2026-04-16 11:00:00',
            'reviewed_at' => '2026-04-16 11:30:00',
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'purchase_debit',
            'amount' => '-500.00',
            'before_balance' => '1700.50',
            'after_balance' => '1200.50',
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
            'occurred_at' => '2026-04-15 10:00:00',
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'withdrawal_debit',
            'amount' => '-100.00',
            'before_balance' => '1200.50',
            'after_balance' => '1100.50',
            'biz_ref_type' => 'withdrawal_request',
            'biz_ref_id' => (string) $withdrawalRequest->id,
            'occurred_at' => '2026-04-16 11:00:00',
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'withdrawal_refund',
            'amount' => '100.00',
            'before_balance' => '1100.50',
            'after_balance' => '1200.50',
            'biz_ref_type' => 'withdrawal_request',
            'biz_ref_id' => (string) $withdrawalRequest->id,
            'occurred_at' => '2026-04-16 11:30:00',
        ]);

        $settlementWithin24h = DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-16',
            'rate' => '0.0200',
            'profit' => '30.00',
        ]);

        $settlementOutOf24h = DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-15',
            'rate' => '0.0200',
            'profit' => '70.00',
        ]);

        $settlementWithin24h->forceFill([
            'created_at' => '2026-04-16 10:00:00',
            'updated_at' => '2026-04-16 10:00:00',
        ])->saveQuietly();

        $settlementOutOf24h->forceFill([
            'created_at' => '2026-04-15 05:00:00',
            'updated_at' => '2026-04-15 05:00:00',
        ])->saveQuietly();

        $this->actingAs($user)
            ->getJson('/home-hero-panel?mode=live')
            ->assertOk()
            ->assertJson([
                'mode' => 'live',
                'badge' => '#live',
                'available_balance' => '1200.50',
                'total_earnings' => '100.00',
                'earnings_24h' => '30.00',
            ])
            ->assertJsonPath('trade_records.0.event_type', 'withdrawal_refund')
            ->assertJsonPath('trade_records.1.event_type', 'withdrawal_debit')
            ->assertJsonPath('trade_records.2.title', 'Mobile AMM')
            ->assertJsonPath('income_records.0.profit', '30.00');

        Carbon::setTestNow();
    }

    public function test_guest_live_mode_returns_zeroed_payload(): void
    {
        $this->getJson('/home-hero-panel?mode=live')
            ->assertOk()
            ->assertJson([
                'mode' => 'live',
                'badge' => '#live',
                'available_balance' => '0.00',
                'total_earnings' => '0.00',
                'earnings_24h' => '0.00',
                'trade_records' => [],
                'income_records' => [],
            ]);
    }

    public function test_invalid_mode_is_rejected(): void
    {
        $this->getJson('/home-hero-panel?mode=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mode']);
    }

    public function test_home_page_persists_selected_hero_panel_mode_in_local_storage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee("const modeStorageKey = 'home_hero_panel_mode';", false)
            ->assertSee("localStorage.getItem(modeStorageKey)", false)
            ->assertSee("localStorage.setItem(modeStorageKey, mode);", false)
            ->assertSee("setMode(savedMode === 'live' ? 'live' : 'damo');", false);
    }
}
