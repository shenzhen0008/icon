<?php

namespace Tests\Feature\Exchange;

use App\Modules\Exchange\Models\ExchangeMetric;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeMetricsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_demo_mode_label_copy(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Welcome to AI Smart Contracts')
            ->assertSee('Artificial intelligence trading')
            ->assertDontSee('交易记录')
            ->assertDontSee('收入记录')
            ->assertDontSee('id="hero-trade-record-btn"', false)
            ->assertDontSee('id="hero-income-record-btn"', false)
            ->assertDontSee('href="/home/hero-panel/trade-records?mode=demo"', false)
            ->assertDontSee('href="/home/hero-panel/income-records?mode=demo"', false)
            ->assertSee('DEMO')
            ->assertSee('#demo')
            ->assertDontSee('#damo');
    }

    public function test_home_page_displays_exchange_metrics_section(): void
    {
        \DB::table('home_display_settings')->where('id', 1)->update([
            'summary_people_count' => '88888',
            'summary_total_profit' => '9999999',
            'shared_exchange_profit_base_value' => '2057.31',
            'shared_exchange_profit_step_seconds' => 3,
            'shared_exchange_profit_min_delta' => '-5.00',
            'shared_exchange_profit_max_delta' => '10.00',
        ]);

        ExchangeMetric::query()
            ->where('exchange_code', 'binance')
            ->update([
                'display_btc_volume' => '$1.50',
                'display_btc_liquidity' => '947',
                'display_eth_volume' => '$2.50',
                'display_eth_liquidity' => '999',
            ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Open transaction!')
            ->assertSee('2000+ base factor library with AI support to more catch derivative factors, one step ahead!')
            ->assertSee('Number of people')
            ->assertSee('总盘获利值')
            ->assertSee('实时操盘平台')
            ->assertSee('Binance')
            ->assertSee('Currency')
            ->assertSee('24h Volume')
            ->assertSee('Liquidity')
            ->assertSee('88,888')
            ->assertSee('9,999,999.00 USDT')
            ->assertDontSee('总盘数据')
            ->assertDontSee('基于下方所有交易所实时汇总。')
            ->assertSee('2,057.31')
            ->assertDontSee('2,057.31 USDT')
            ->assertDontSee('$2,057')
            ->assertDontSee('$2057')
            ->assertSee('data-shared-profit-base-value="2057.31"', false)
            ->assertSee('data-shared-profit-step-seconds="3"', false)
            ->assertSee('base-anchored-ticker:ready', false)
            ->assertSee('new Date()', false)
            ->assertDontSee('2026-04-16 12:34:56');
    }

    public function test_home_page_summary_values_use_smaller_type_scale_than_display_headings(): void
    {
        $response = $this->get('/')
            ->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString(
            'id="summary-participant-count"',
            $content,
        );
        $this->assertStringContainsString(
            'id="summary-total-profit"',
            $content,
        );
        $this->assertStringContainsString(
            'class="text-scale-title font-semibold text-[rgb(var(--theme-primary))]" id="summary-participant-count"',
            $content,
        );
        $this->assertStringContainsString(
            'class="text-scale-title font-semibold text-[rgb(var(--theme-accent))]" id="summary-total-profit"',
            $content,
        );
        $this->assertStringNotContainsString(
            'class="text-scale-display font-semibold text-[rgb(var(--theme-primary))]" id="summary-participant-count"',
            $content,
        );
        $this->assertStringNotContainsString(
            'class="text-scale-display font-semibold text-[rgb(var(--theme-accent))]" id="summary-total-profit"',
            $content,
        );
    }

    public function test_home_page_advances_summary_values_when_step_seconds_has_elapsed(): void
    {
        Carbon::setTestNow('2026-04-16 12:00:03');

        \DB::table('home_display_settings')->where('id', 1)->update([
            'summary_people_count' => '100',
            'summary_people_step_seconds' => 3,
            'summary_people_min_delta' => '5.00',
            'summary_people_max_delta' => '5.00',
            'summary_people_last_tick_at' => '2026-04-16 12:00:00',
            'summary_total_profit' => '2000.00',
            'summary_profit_step_seconds' => 3,
            'summary_profit_min_delta' => '10.00',
            'summary_profit_max_delta' => '10.00',
            'summary_profit_last_tick_at' => '2026-04-16 12:00:00',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('105')
            ->assertSee('2,010.00 USDT');

        $this->assertDatabaseHas('home_display_settings', [
            'id' => 1,
            'summary_people_count' => '105',
            'summary_total_profit' => '2010.00',
        ]);

        Carbon::setTestNow();
    }

    public function test_home_page_no_longer_uses_exchange_metrics_polling_script(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('requestAnimationFrame', false)
            ->assertDontSee('animateValue', false)
            ->assertDontSee('/exchange-metrics', false)
            ->assertDontSee('fetch(\'/exchange-metrics\'', false)
            ->assertSee('setInterval(refreshSummary, 3000);', false)
            ->assertSee("fetch('/home-summary'", false);
    }

    public function test_exchange_metrics_feed_route_is_removed(): void
    {
        $this->get('/exchange-metrics')
            ->assertNotFound();
    }

    public function test_home_summary_feed_returns_latest_formatted_values(): void
    {
        Carbon::setTestNow('2026-04-16 12:00:03');

        \DB::table('home_display_settings')->where('id', 1)->update([
            'summary_people_count' => '100',
            'summary_people_step_seconds' => 3,
            'summary_people_min_delta' => '5.00',
            'summary_people_max_delta' => '5.00',
            'summary_people_last_tick_at' => '2026-04-16 12:00:00',
            'summary_total_profit' => '2000.00',
            'summary_profit_step_seconds' => 3,
            'summary_profit_min_delta' => '10.00',
            'summary_profit_max_delta' => '10.00',
            'summary_profit_last_tick_at' => '2026-04-16 12:00:00',
        ]);

        $this->get('/home-summary')
            ->assertOk()
            ->assertJson([
                'participant_count' => '105',
                'total_profit' => '2,010.00',
            ]);

        Carbon::setTestNow();
    }

    public function test_home_page_hides_quick_pay_entry_until_next_release(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="home-onchain-entry"', false)
            ->assertDontSee('直接付款（链上充值）')
            ->assertDontSee('id="home-quick-pay-panel"', false)
            ->assertDontSee('确认充值并拉起钱包付款');
    }
}
