<?php

namespace Tests\Feature\Admin;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_product_management_pages_in_local_environment(): void
    {
        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('产品管理');
        $this->get('/admin/products')
            ->assertOk()
            ->assertSee('手动触发当日结算');
        $this->get('/admin/products/create')
            ->assertOk()
            ->assertDontSee('按钮文案')
            ->assertDontSee('产品介绍')
            ->assertSee('多语言介绍')
            ->assertSee('语言')
            ->assertSee('介绍文案');
        $this->get('/admin/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertDontSee('按钮文案')
            ->assertDontSee('产品介绍')
            ->assertSee('多语言介绍');
    }
}
