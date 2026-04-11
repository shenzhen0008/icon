<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_navigation_is_rendered_on_public_pages(): void
    {
        $product = Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        foreach (['/', '/products', '/products/'.$product->id, '/support', '/stream-chat', '/login', '/register'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('首页')
                ->assertSee('产品')
                ->assertSee('帮助')
                ->assertSee('我的')
                ->assertSee('客服')
                ->assertSee('/help')
                ->assertDontSee('href="/recharge"', false)
                ->assertSee('/support')
                ->assertDontSee('后台')
                ->assertDontSee('/admin')
                ->assertSee('Stream Chat')
                ->assertSee('/stream-chat')
                ->assertDontSee('Stream Agent');
        }

        $this->get('/me')
            ->assertOk()
            ->assertSee('首页')
            ->assertSee('产品')
            ->assertSee('帮助')
            ->assertSee('我的')
            ->assertSee('客服')
            ->assertSee('/help')
            ->assertSee('href="/recharge"', false)
            ->assertSee('充值');
    }

    public function test_global_navigation_is_rendered_on_confirm_password_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/confirm-password')
            ->assertOk()
            ->assertSee('首页')
            ->assertSee('产品')
            ->assertSee('帮助')
            ->assertSee('我的')
            ->assertSee('客服')
            ->assertSee('/help')
            ->assertDontSee('href="/recharge"', false)
            ->assertSee('/support')
            ->assertDontSee('后台')
            ->assertDontSee('/admin')
            ->assertSee('Stream Chat')
            ->assertSee('/stream-chat')
            ->assertSee('Stream Agent')
            ->assertSee('/stream-chat-agent');
    }

    public function test_mobile_navigation_uses_theme_variable_classes(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('id="top-nav"', false)
            ->assertSee('id="mobile-nav"', false)
            ->assertSee('border-theme')
            ->assertSee('bg-theme-secondary/90')
            ->assertSee('text-theme-secondary')
            ->assertSee('text-[rgb(var(--theme-primary))]')
            ->assertSee("--top-nav-height', topHeight", false)
            ->assertSee("--mobile-nav-height', mobileHeight", false)
            ->assertDontSee('bg-slate-950/95')
            ->assertDontSee('text-cyan-300')
            ->assertDontSee('text-slate-300')
            ->assertDontSee('border-white/10');

        $content = $response->getContent();
        $mobileNavStart = strpos($content, '<nav id="mobile-nav"');
        $this->assertNotFalse($mobileNavStart);

        $mobileNavEnd = strpos($content, '</nav>', $mobileNavStart);
        $this->assertNotFalse($mobileNavEnd);

        $mobileNavMarkup = substr($content, $mobileNavStart, $mobileNavEnd - $mobileNavStart + 6);

        $this->assertStringContainsString('>帮助<', $mobileNavMarkup);
        $this->assertStringContainsString('href="/help"', $mobileNavMarkup);
        $this->assertStringContainsString('Stream', $mobileNavMarkup);
        $this->assertStringNotContainsString('>客服<', $mobileNavMarkup);
    }

    public function test_home_page_declares_saved_theme_variable_only_once(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'const savedTheme'));
        $response->assertSee("const streamNotifyBootstrapKey = 'stream_chat_notify_bootstrap_ready';", false);
        $response->assertSee("localStorage.getItem(streamNotifyBootstrapKey) === '1'", false);
    }

    public function test_public_pages_disable_mobile_zoom_via_viewport_meta(): void
    {
        $product = Product::query()->create([
            'name' => 'Product Zoom Guard',
            'code' => 'PZG',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $viewport = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';

        foreach (['/', '/products', '/products/'.$product->id, '/support', '/stream-chat', '/login', '/register'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee($viewport);
        }
    }
}
