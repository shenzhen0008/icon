<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductReservationManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_product_reservation_management_page_in_local_environment(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        DB::table('product_reservations')->insert([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount_usdt' => 1000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/admin')->assertOk();

        $this->get('/admin/product-reservations')
            ->assertOk()
            ->assertSee('预订订单管理');
    }
}
