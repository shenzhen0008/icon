<?php

namespace Tests\Feature\Position;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_purchase_product_when_balance_is_sufficient_for_total_amount(): void
    {
        $user = User::factory()->create([
            'balance' => 6000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product B',
            'code' => 'PB',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->from('/products/'.$product->id)->post('/positions/purchase', [
            'product_id' => $product->id,
            'shares' => 1,
        ]);

        $response->assertRedirect('/products/'.$product->id);

        $user->refresh();
        $this->assertSame('5000.00', number_format((float) $user->balance, 2, '.', ''));

        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
        ]);

        $position = Position::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertDatabaseHas('balance_ledgers', [
            'user_id' => $user->id,
            'type' => 'purchase_debit',
            'amount' => -1000,
            'before_balance' => 6000,
            'after_balance' => 5000,
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
        ]);
    }

    public function test_guest_cannot_purchase_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Product A',
            'code' => 'PA',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->post('/positions/purchase', [
            'product_id' => $product->id,
            'shares' => 1,
        ])->assertRedirect('/login');
    }

    public function test_purchase_fails_when_input_is_invalid(): void
    {
        $user = User::factory()->create([
            'balance' => 6000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product B',
            'code' => 'PB',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'shares' => 0,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['shares']);
    }

    public function test_purchase_fails_when_balance_is_insufficient_for_total_amount(): void
    {
        $user = User::factory()->create([
            'balance' => 3000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product B',
            'code' => 'PB',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'shares' => 4,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['shares']);

        $this->assertDatabaseCount('balance_ledgers', 0);
    }
}
