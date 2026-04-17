<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_product_catalog_page(): void
    {
        Product::query()->create([
            'name' => 'Alpha Pool',
            'code' => 'ALPHA',
            'unit_price' => 1000,
            'is_active' => true,
            'sort' => 20,
            'purchase_limit_count' => 3,
            'limit_min_usdt' => 3000,
            'limit_max_usdt' => 50000,
            'rate_min_percent' => 1.20,
            'rate_max_percent' => 1.50,
            'cycle_days' => 7,
            'product_icon_path' => '/images/products/symbols/symbol-01.png',
            'symbol_icon_paths' => ['/images/products/symbols/symbol-01.png'],
        ]);

        Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 2000,
            'is_active' => true,
            'sort' => 0,
            'purchase_limit_count' => null,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.83,
            'rate_max_percent' => 2.01,
            'cycle_days' => 2,
            'product_icon_path' => '/images/products/symbols/symbol-02.png',
            'symbol_icon_paths' => [
                '/images/products/symbols/symbol-01.png',
                '/images/products/symbols/symbol-02.png',
                '/images/products/symbols/symbol-03.png',
                '/images/products/symbols/symbol-04.png',
            ],
        ]);

        Product::query()->create([
            'name' => 'Product C',
            'code' => 'PC',
            'unit_price' => 3000,
            'is_active' => false,
            'sort' => 5,
        ]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertSee('产品市场');
        $response->assertSeeInOrder(['Mobile AMM', 'Alpha Pool']);
        $response->assertDontSee('Product C');
        $response->assertSee('今日预计收益');
        $response->assertSee('累计收益');
        $response->assertSee('订单数量');
        $response->assertSee('规则');
        $response->assertSee('href="/products/rules"', false);
        $response->assertSee('订单');
        $response->assertSee('自动质押');
        $response->assertSee('限额(USDT)');
        $response->assertSee('限购：不限次');
        $response->assertSee('限购：3次');
        $response->assertSee('收益率');
        $response->assertSee('周期');
        $response->assertSee('1,000-10,000');
        $response->assertSee('1.83-2.01%');
        $response->assertSee('2天');
        $response->assertSee('立即购买');
        $response->assertSee('data-open-activate-modal', false);
        $response->assertSee('id="activate-modal"', false);
        $response->assertSee('theme-pin-modal');
        $response->assertSee('text-theme-on-primary');
        $response->assertDontSee('rounded-2xl bg-[rgb(var(--theme-primary))] px-4 py-2 text-xl font-medium text-theme-secondary');
        $response->assertSee('/images/products/symbols/symbol-04.png');
        $response->assertSee('h-[clamp(1.95rem,6.8vw,2.45rem)] w-[clamp(1.95rem,6.8vw,2.45rem)]', false);
        $response->assertSee('h-[clamp(1.45rem,5.2vw,1.82rem)] w-[clamp(1.45rem,5.2vw,1.82rem)]', false);
        $response->assertSee('rounded-2xl border border-theme bg-theme-secondary/20 px-2 py-[0.1rem]', false);
        $response->assertSee('overflow-x-auto overflow-y-hidden whitespace-nowrap pb-[0.05rem]', false);
    }

    public function test_guest_can_view_product_detail_without_activate_prompt_section(): void
    {
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.15,
            'rate_max_percent' => 2.22,
            'cycle_days' => 2,
            'description' => '这是产品介绍内容',
            'product_icon_path' => '/images/products/symbols/symbol-01.png',
            'symbol_icon_paths' => [
                '/images/products/symbols/symbol-01.png',
                '/images/products/symbols/symbol-02.png',
            ],
        ]);

        $response = $this->get('/products/'.$product->id);

        $response->assertOk();
        $response->assertSee('Mobile AMM');
        $response->assertSee('限额(USDT)');
        $response->assertSee('1,000-10,000');
        $response->assertSee('收益率');
        $response->assertSee('1.15-2.22%');
        $response->assertSee('周期');
        $response->assertSee('2天');
        $response->assertSee('产品介绍');
        $response->assertSee('这是产品介绍内容');
        $response->assertSee('/images/products/symbols/symbol-01.png');
        $response->assertDontSee('请先激活临时账号后购买。');
        $response->assertDontSee('设置密码并注册');
        $response->assertDontSee('id="activate-modal"', false);
        $response->assertDontSee('theme-pin-modal');
    }

    public function test_guest_activation_from_product_detail_redirects_back_to_current_product_page(): void
    {
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->get('/products/'.$product->id)->assertOk();

        $response = $this->from('/products/'.$product->id)->post('/register', [
            'password' => '123456',
            'password_confirmation' => '123456',
            'redirect_to' => '/products/'.$product->id,
        ]);

        $response->assertRedirect('/products/'.$product->id);
        $this->assertAuthenticated();
    }

    public function test_authenticated_user_sees_theme_text_class_on_purchase_button_in_product_detail(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.15,
            'rate_max_percent' => 2.22,
            'cycle_days' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/products/'.$product->id);

        $response->assertOk();
        $response->assertSee('立即购买');
        $response->assertSee('text-theme-on-primary');
        $response->assertDontSee('rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 text-sm font-semibold text-theme-secondary');
    }

    public function test_catalog_falls_back_to_default_symbol_icons_when_product_has_no_symbol_icon_paths(): void
    {
        Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 2000,
            'is_active' => true,
            'sort' => 0,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.83,
            'rate_max_percent' => 2.01,
            'cycle_days' => 2,
            'product_icon_path' => '/images/products/symbols/symbol-02.png',
            'symbol_icon_paths' => null,
        ]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertSee('/images/products/symbols/symbol-01.png');
        $response->assertSee('/images/products/symbols/symbol-07.png');
    }

    public function test_authenticated_user_sees_real_profit_summary_in_catalog(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 2000,
            'is_active' => true,
            'sort' => 0,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.83,
            'rate_max_percent' => 2.01,
            'cycle_days' => 2,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 3000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => now()->toDateString(),
            'rate' => 0.02,
            'profit' => 120.50,
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => now()->subDay()->toDateString(),
            'rate' => 0.02,
            'profit' => 80,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/products');

        $response->assertOk();
        $response->assertSee('$120.50');
        $response->assertSee('$200.50');
        $response->assertSee('1');
    }

    public function test_reserve_mode_product_renders_preorder_actions_on_catalog_and_detail(): void
    {
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1200,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('立即预订');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/products/'.$product->id)
            ->assertOk()
            ->assertSee('预订')
            ->assertSee('立即预订')
            ->assertDontSee('立即购买');
    }

    public function test_catalog_page_localizes_fixed_copy_but_keeps_product_name_from_backend_data(): void
    {
        Product::query()->create([
            'name' => '量化策略A',
            'code' => 'QSA',
            'unit_price' => 1000,
            'is_active' => true,
            'sort' => 0,
            'purchase_limit_count' => 2,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.25,
            'rate_max_percent' => 1.55,
            'cycle_days' => 3,
        ]);

        $response = $this->get('/products?locale=en');

        $response->assertOk();
        $response->assertSee('Product Market');
        $response->assertSee('Rules');
        $response->assertSee('Orders');
        $response->assertSee('Auto Staking');
        $response->assertSee('Buy Now');
        $response->assertSee('limit (USDT)');
        $response->assertSee('Rate of return');
        $response->assertSee('Cycle');
        $response->assertSee('Purchase Limit: 2');
        $response->assertSee('量化策略A');
    }

    public function test_product_detail_localizes_fixed_copy_but_keeps_product_name_from_backend_data(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => '中文产品名A',
            'code' => 'CNPA',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
            'rate_min_percent' => 1.10,
            'rate_max_percent' => 2.20,
            'cycle_days' => 2,
            'description' => '这里是中文产品介绍',
        ]);

        $response = $this->actingAs($user)->get('/products/'.$product->id.'?locale=en');

        $response->assertOk();
        $response->assertSee('Product Detail');
        $response->assertSee('limit (USDT)');
        $response->assertSee('Rate of return');
        $response->assertSee('Cycle');
        $response->assertSee('2d');
        $response->assertSee('Product Introduction');
        $response->assertSee('Buy');
        $response->assertSee('Buy Now');
        $response->assertSee('Current Balance');
        $response->assertSee('Escrow Amount (USDT)');
        $response->assertSee('中文产品名A');
        $response->assertSee('这里是中文产品介绍');
    }
}
