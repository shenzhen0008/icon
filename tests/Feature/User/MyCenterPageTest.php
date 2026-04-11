<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MyCenterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_my_center_with_temp_username(): void
    {
        $response = $this->get('/me');

        $response->assertOk();
        $response->assertSee('账号信息');
        $response->assertSee('收益状态');
        $response->assertSee('持仓产品');
        $response->assertSee('临时账号');
        $response->assertSee('设置密码并注册');
        $response->assertDontSee('退出登录');

        $this->assertTrue(session()->has('temp_username'));
    }

    public function test_guest_can_activate_from_my_center_modal_form(): void
    {
        $this->get('/me')->assertOk();
        $tempUsername = session('temp_username');

        $response = $this->from('/me')->post('/register', [
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertRedirect('/me');
        $this->assertAuthenticated();

        $user = User::query()->where('username', $tempUsername)->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password1234', $user->password));
    }

    public function test_activation_from_my_center_rejects_invalid_input(): void
    {
        $this->get('/me')->assertOk();

        $this->from('/me')->post('/register', [
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertRedirect('/me')
            ->assertSessionHasErrors(['password']);
    }

    public function test_authenticated_user_sees_formal_username_on_my_center(): void
    {
        $user = User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'remark' => '这是管理员备注',
            'password' => bcrypt('password1234'),
        ]);

        $this->actingAs($user)
            ->get('/me')
            ->assertOk()
            ->assertSee('账号信息')
            ->assertSee('收益状态')
            ->assertSee('持仓产品')
            ->assertSee('正式账号')
            ->assertSee('账户余额')
            ->assertSee('充值')
            ->assertSee('/recharge')
            ->assertSee('AbC123xYz987QwErT654X')
            ->assertSee('退出登录')
            ->assertDontSee('这是管理员备注')
            ->assertDontSee('前往充值')
            ->assertDontSee('设置密码并注册');
    }

    public function test_my_center_shows_redeeming_positions_but_hides_redeemed_positions(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 2000,
            'status' => 'redeeming',
            'opened_at' => now(),
        ]);

        Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 3000,
            'status' => 'redeemed',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/me')
            ->assertOk()
            ->assertSee('持有中')
            ->assertSee('赎回中')
            ->assertDontSee('已赎回');
    }

    public function test_my_center_position_card_shows_latest_three_daily_profits(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-01',
            'rate' => 0.01,
            'profit' => 10,
        ]);
        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-02',
            'rate' => 0.01,
            'profit' => 11,
        ]);
        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-03',
            'rate' => 0.01,
            'profit' => 12,
        ]);
        DailySettlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'position_id' => $position->id,
            'settlement_date' => '2026-04-04',
            'rate' => 0.01,
            'profit' => 13,
        ]);

        $this->actingAs($user)
            ->get('/me')
            ->assertOk()
            ->assertSee('最近3天收益')
            ->assertSee('04-04')
            ->assertSee('04-03')
            ->assertSee('04-02')
            ->assertDontSee('04-01');
    }
}
