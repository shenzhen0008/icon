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
            'amount' => 1000,
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
            'amount' => 1000,
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
            'amount' => 0,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);
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
            'amount' => 4000,
        ])->assertRedirect('/recharge');

        $user->refresh();
        $this->assertSame('3000.00', number_format((float) $user->balance, 2, '.', ''));
        $this->assertDatabaseCount('positions', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_purchase_count_is_not_limited_for_direct_purchase(): void
    {
        $user = User::factory()->create([
            'balance' => 20000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product C',
            'code' => 'PC',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect();

        $this->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect();

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id);

        $this->assertDatabaseCount('positions', 3);
        $this->assertDatabaseCount('balance_ledgers', 3);
    }

    public function test_purchase_fails_when_user_reaches_product_purchase_limit_count(): void
    {
        $user = User::factory()->create([
            'balance' => 20000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product Limit',
            'code' => 'PLM',
            'unit_price' => 1000,
            'is_active' => true,
            'purchase_limit_count' => 2,
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('positions', 2);
        $this->assertDatabaseCount('balance_ledgers', 2);
    }

    public function test_purchase_limit_count_checks_cumulative_purchase_times(): void
    {
        $user = User::factory()->create([
            'balance' => 12000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product Cumulative',
            'code' => 'PCM',
            'unit_price' => 1000,
            'is_active' => true,
            'purchase_limit_count' => 1,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'redeemed',
            'opened_at' => now()->subDays(5),
            'closed_at' => now()->subDays(2),
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('positions', 1);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_purchase_fails_when_total_amount_is_below_product_min_limit(): void
    {
        $user = User::factory()->create([
            'balance' => 10000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product D',
            'code' => 'PD',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_min_usdt' => 3000,
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 2000,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('positions', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_purchase_fails_when_user_total_amount_for_product_exceeds_product_max_limit(): void
    {
        $user = User::factory()->create([
            'balance' => 10000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product E',
            'code' => 'PE',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_max_usdt' => 3000,
        ]);

        $this->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 2000,
        ])->assertRedirect();

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 2000,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('positions', 1);
        $this->assertDatabaseCount('balance_ledgers', 1);
    }

    public function test_user_can_purchase_product_twice_within_max_total_amount(): void
    {
        $user = User::factory()->create([
            'balance' => 20000,
        ]);

        $product = Product::query()->create([
            'name' => 'Product F',
            'code' => 'PF',
            'unit_price' => 1000,
            'is_active' => true,
            'limit_min_usdt' => 1000,
            'limit_max_usdt' => 10000,
        ]);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 9000,
        ])->assertRedirect('/products/'.$product->id);

        $this->assertDatabaseCount('positions', 2);
        $this->assertDatabaseCount('balance_ledgers', 2);

        $this->from('/products/'.$product->id)->actingAs($user)->post('/positions/purchase', [
            'product_id' => $product->id,
            'amount' => 1000,
        ])->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);
    }

    public function test_reserve_mode_product_cannot_be_purchased_directly(): void
    {
        $user = User::factory()->create([
            'balance' => 10000,
        ]);

        $product = Product::query()->create([
            'name' => 'Reserve Product',
            'code' => 'RSP',
            'unit_price' => 1000,
            'is_active' => true,
            'trade_mode' => 'reserve',
        ]);

        $this->actingAs($user)
            ->from('/products/'.$product->id)
            ->post('/positions/purchase', [
                'product_id' => $product->id,
                'amount' => 1000,
            ])
            ->assertRedirect('/products/'.$product->id)
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('positions', 0);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }
}
