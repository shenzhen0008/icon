<?php

namespace Tests\Feature\Admin;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeMetricManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_exchange_metric_management_pages_in_local_environment(): void
    {
        $metric = ExchangeMetric::query()->create([
            'exchange_code' => 'bitget',
            'exchange_name' => 'Bitget',
            'btc_value' => 1024.12,
            'btc_liquidity' => 320,
            'eth_value' => 512.34,
            'eth_liquidity' => 180,
            'total_value' => 1536.46,
            'profit_value' => 8899.12,
            'sort' => 70,
            'is_active' => true,
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('操盘平台');

        $this->get('/admin/exchange-metrics')
            ->assertOk()
            ->assertSee('交易所代码')
            ->assertSee('利润值');

        $this->get('/admin/exchange-metrics/create')
            ->assertOk()
            ->assertSee('交易所代码')
            ->assertSee('BTC 价格');

        $this->get('/admin/exchange-metrics/'.$metric->id.'/edit')
            ->assertOk()
            ->assertSee('交易所名称')
            ->assertSee('ETH 流动性');
    }
}
