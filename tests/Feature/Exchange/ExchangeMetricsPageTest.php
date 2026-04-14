<?php

namespace Tests\Feature\Exchange;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeMetricsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_demo_mode_label_copy(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Welcome to AI Smart Contracts')
            ->assertSee('Artificial intelligence trading')
            ->assertSee('DEMO')
            ->assertSee('#demo')
            ->assertDontSee('#damo');
    }

    public function test_home_page_displays_exchange_metrics_section(): void
    {
        ExchangeMetric::query()
            ->where('exchange_code', 'binance')
            ->update([
                'btc_value' => 1.5,
                'btc_liquidity' => 947,
                'eth_value' => 2.5,
                'eth_liquidity' => 999,
                'total_value' => 4.0,
                'profit_value' => 2057.31,
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
            ->assertDontSee('总盘数据')
            ->assertDontSee('基于下方所有交易所实时汇总。')
            ->assertSee('2,057.31')
            ->assertSee('2,057.31 USDT')
            ->assertDontSee('$2,057')
            ->assertDontSee('$2057');
    }

    public function test_exchange_metrics_feed_returns_active_rows(): void
    {
        ExchangeMetric::query()
            ->where('exchange_code', 'binance')
            ->update([
                'btc_value' => 3.12345678,
                'btc_liquidity' => 947,
                'eth_value' => 2.00000001,
                'eth_liquidity' => 999,
                'total_value' => 5.12345679,
                'profit_value' => 2057.15,
            ]);

        $response = $this->get('/exchange-metrics');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                [
                    'exchange_code',
                    'exchange_name',
                    'logo_url',
                    'btc_value',
                    'btc_liquidity',
                    'eth_value',
                    'eth_liquidity',
                    'profit_value',
                    'updated_at',
                ],
            ],
            'server_time',
        ]);

        $response->assertJsonFragment([
            'exchange_code' => 'binance',
            'profit_value' => '2,057.15',
        ]);
    }

    public function test_home_page_refresh_updates_profit_without_animation_script(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('requestAnimationFrame', false)
            ->assertDontSee('animateValue', false)
            ->assertSee('setInterval(refresh, 3000);', false)
            ->assertDontSee('setInterval(refresh, 2000);', false);
    }
}
