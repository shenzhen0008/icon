<?php

namespace Tests\Feature\Admin;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExchangeMetricManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_exchange_metric_management_pages_in_local_environment(): void
    {
        $metric = ExchangeMetric::query()->create([
            'exchange_code' => 'bitget',
            'exchange_name' => 'Bitget',
            'display_btc_liquidity' => '320',
            'display_eth_liquidity' => '180',
            'liquidity_step_seconds' => 5,
            'liquidity_min_delta' => '-5.00',
            'liquidity_max_delta' => '10.00',
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
            ->assertDontSee('BTC 24h Volume')
            ->assertDontSee('ETH 24h Volume')
            ->assertSee('Liquidity 跳动设置')
            ->assertDontSee('更新时间');

        $this->get('/admin/exchange-metrics/'.$metric->id.'/edit')
            ->assertOk()
            ->assertSee('交易所名称')
            ->assertSee('ETH Liquidity')
            ->assertSee('24h Volume 会按首页展示数值里的 BTC/ETH 倍率自动计算。')
            ->assertDontSee('display_updated_at', false);
    }

    public function test_admin_can_save_exchange_detail_ticker_settings(): void
    {
        $metric = ExchangeMetric::query()->create([
            'exchange_code' => 'bitget',
            'exchange_name' => 'Bitget',
            'display_btc_liquidity' => '320',
            'display_eth_liquidity' => '180',
            'sort' => 70,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\ExchangeMetrics\Pages\EditExchangeMetric::class, [
            'record' => $metric->id,
        ])
            ->fillForm([
                'exchange_code' => 'bitget',
                'exchange_name' => 'Bitget',
                'display_btc_liquidity' => '320',
                'display_eth_liquidity' => '180',
                'liquidity_step_seconds' => '5',
                'liquidity_min_delta' => '-5',
                'liquidity_max_delta' => '10',
                'sort' => 70,
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('exchange_metrics', [
            'id' => $metric->id,
            'liquidity_step_seconds' => 5,
            'liquidity_min_delta' => '-5.00',
            'liquidity_max_delta' => '10.00',
        ]);
    }

    public function test_exchange_detail_ticker_step_seconds_must_be_positive(): void
    {
        $metric = ExchangeMetric::query()->create([
            'exchange_code' => 'bitget',
            'exchange_name' => 'Bitget',
            'display_btc_liquidity' => '320',
            'display_eth_liquidity' => '180',
            'sort' => 70,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\ExchangeMetrics\Pages\EditExchangeMetric::class, [
            'record' => $metric->id,
        ])
            ->fillForm([
                'liquidity_step_seconds' => '0',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'liquidity_step_seconds',
            ]);
    }
}
