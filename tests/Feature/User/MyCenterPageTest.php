<?php

namespace Tests\Feature\User;

use App\Models\User;
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
        $response->assertDontSee('持仓产品');
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
            ->assertDontSee('持仓产品')
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

    public function test_my_center_places_account_panel_after_profit_panel(): void
    {
        $response = $this->get('/me');

        $response->assertOk()
            ->assertSeeInOrder(['收益状态', '账号信息']);
    }
}
