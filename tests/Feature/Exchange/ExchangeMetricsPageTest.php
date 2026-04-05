<?php

namespace Tests\Feature\Exchange;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeMetricsPageTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee('总盘数据')
            ->assertSee('Number of people')
            ->assertSee('总盘获利值')
            ->assertSee('实时操盘平台')
            ->assertSee('Binance')
            ->assertSee('Currency')
            ->assertSee('24h Volume')
            ->assertSee('Liquidity')
            ->assertSee('2057');
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
            'profit_value' => '2057',
        ]);
    }
}
