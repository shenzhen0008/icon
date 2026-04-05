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

        foreach (['/', '/products', '/products/'.$product->id, '/me', '/login', '/register'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('首页')
                ->assertSee('产品')
                ->assertSee('我的');
        }
    }

    public function test_global_navigation_is_rendered_on_confirm_password_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/confirm-password')
            ->assertOk()
            ->assertSee('首页')
            ->assertSee('产品')
            ->assertSee('我的');
    }
}
