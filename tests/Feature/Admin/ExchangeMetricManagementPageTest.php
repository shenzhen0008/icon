<?php

namespace Tests\Feature\Admin;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeMetricManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_exchange_metric_management_pages_in_local_environment(): void
    {
        $metric = ExchangeMetric::query()->create([
            'exchange_code' => 'bitget',
            'exchange_name' => 'Bitget',
            'display_btc_volume' => '$1,024.12',
            'display_btc_liquidity' => '320',
            'display_eth_volume' => '$512.34',
            'display_eth_liquidity' => '180',
            'sort' => 70,
            'is_active' => true,
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('操盘平台');

        $this->get('/admin/exchange-metrics')
            ->assertOk()
            ->assertSee('交易所代码')
            ->assertDontSee('展示获利值');

        $this->get('/admin/exchange-metrics/create')
            ->assertOk()
            ->assertSee('交易所代码')
            ->assertSee('BTC 24h Volume')
            ->assertDontSee('更新时间');

        $this->get('/admin/exchange-metrics/'.$metric->id.'/edit')
            ->assertOk()
            ->assertSee('交易所名称')
            ->assertSee('ETH Liquidity')
            ->assertDontSee('display_updated_at', false);
    }
}
