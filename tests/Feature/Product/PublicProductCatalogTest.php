<?php

namespace Tests\Feature\Product;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_product_catalog_page(): void
    {
        Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
            'unit_price' => 1000,
            'is_active' => true,
            'sort' => 20,
        ]);

        Product::query()->create([
            'name' => 'Product B',
            'code' => 'PB',
            'unit_price' => 2000,
            'is_active' => true,
            'sort' => 10,
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
        $response->assertSeeInOrder(['Product B', 'Product A']);
        $response->assertDontSee('Product C');
    }

    public function test_guest_can_view_product_detail_and_is_prompted_to_login_for_purchase(): void
    {
        $product = Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $response = $this->get('/products/'.$product->id);

        $response->assertOk();
        $response->assertSee('每份价格');
        $response->assertSee('1000.00');
        $response->assertSee('去登录');
    }
}
