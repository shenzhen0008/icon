<?php

namespace Tests\Feature\I18n;

use App\Modules\Exchange\Models\ExchangeMetric;
use App\Modules\Home\Models\HomeDisplaySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_english_copy_when_locale_is_en(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=en');

        $response->assertOk();
        $response->assertSee('Digital Asset Management Platform');
        $response->assertSee('Balance (SDT)');
        $response->assertSee('Live Trading Platforms');
        $response->assertSee('Total Profit');
        $response->assertSee('Support');
    }

    public function test_home_page_renders_japanese_copy_when_locale_is_ja(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=ja');

        $response->assertOk();
        $response->assertSee('デジタル資産管理プラットフォーム');
        $response->assertSee('残高 (USDT)');
        $response->assertSee('ライブ取引プラットフォーム');
    }

    public function test_home_page_renders_korean_copy_when_locale_is_ko(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=ko');

        $response->assertOk();
        $response->assertSee('디지털 자산 관리 플랫폼');
        $response->assertSee('잔액 (USDT)');
        $response->assertSee('실시간 거래 플랫폼');
    }

    public function test_home_page_renders_french_copy_when_locale_is_fr(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=fr');

        $response->assertOk();
        $response->assertSee('Plateforme de gestion d’actifs numériques');
        $response->assertSee('Solde (USDT)');
        $response->assertSee('Plateformes de trading en direct');
    }

    public function test_home_page_renders_german_copy_when_locale_is_de(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=de');

        $response->assertOk();
        $response->assertSee('Plattform für digitales Vermögensmanagement');
        $response->assertSee('Kontostand (USDT)');
        $response->assertSee('Live-Handelsplattformen');
    }

    public function test_home_page_falls_back_to_default_copy_for_untranslated_locale(): void
    {
        $this->seedHomeDependencies();

        $response = $this->get('/?locale=it');

        $response->assertOk();
        $response->assertSee('数字资产管理平台');
        $response->assertSee('可用余额 (USDT)');
        $response->assertSee('实时操盘平台');
    }

    private function seedHomeDependencies(): void
    {
        HomeDisplaySetting::query()->updateOrCreate(['id' => 1], [
            'id' => 1,
            'summary_people_count' => '100',
            'summary_people_step_seconds' => 3,
            'summary_people_min_delta' => 1,
            'summary_people_max_delta' => 2,
            'summary_total_profit' => '1000.00 USDT',
            'summary_profit_step_seconds' => 3,
            'summary_profit_min_delta' => 1,
            'summary_profit_max_delta' => 2,
            'shared_exchange_profit_base_value' => 888.88,
            'shared_exchange_profit_step_seconds' => 3,
            'shared_exchange_profit_min_delta' => 1,
            'shared_exchange_profit_max_delta' => 2,
        ]);

        ExchangeMetric::query()->firstOrCreate(['exchange_code' => 'BINANCE'], [
            'exchange_name' => 'Binance',
            'display_btc_volume' => '$100',
            'display_btc_liquidity' => '200',
            'display_eth_volume' => '$300',
            'display_eth_liquidity' => '400',
            'is_active' => true,
            'sort' => 1,
        ]);
    }
}
