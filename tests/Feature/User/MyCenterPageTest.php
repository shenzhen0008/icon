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
        $response->assertSee('存取款方式');
        $response->assertDontSee('持仓产品');
        $response->assertSee('临时账号');
        $response->assertDontSee('设置密码并注册');
        $response->assertSee('theme-pin-modal');
        $response->assertSee('请输入并确认 6 位数字交易 PIN');
        $response->assertSee('输入 6 位 PIN');
        $response->assertSee('确认 6 位 PIN');
        $response->assertSee('设置交易 PIN');
        $response->assertSee('已有账号，去登录');
        $response->assertSee('data-switch-panel="login"', false);
        $response->assertSee('const shouldAutoOpen = true;', false);
        $response->assertSee('const closeRedirectUrl =', false);
        $response->assertSee('locale=zh-CN', false);
        $response->assertDontSee('const lockOpen = true;', false);
        $response->assertDontSee('退出登录');

        $this->assertTrue(session()->has('temp_username'));
    }

    public function test_guest_can_activate_from_my_center_modal_form(): void
    {
        $this->get('/me')->assertOk();
        $tempUsername = session('temp_username');

        $response = $this->from('/me')->post('/register', [
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect('/me');
        $this->assertAuthenticated();

        $user = User::query()->where('username', $tempUsername)->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('123456', $user->password));
    }

    public function test_activation_from_my_center_rejects_invalid_input(): void
    {
        $this->get('/me')->assertOk();

        $this->from('/me')->post('/register', [
            'password' => '12ab56',
            'password_confirmation' => '12ab56',
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
            ->assertSee('存取款方式')
            ->assertDontSee('持仓产品')
            ->assertSee('正式账号')
            ->assertSee('加密货币存款')
            ->assertSee('id="payment-method-form"', false)
            ->assertSee('action="/recharge/entry"', false)
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
            ->assertSeeInOrder(['存取款方式', '账号信息']);
    }

    public function test_my_center_uses_shared_home_hero_panel_mode_storage_key(): void
    {
        $this->get('/me')
            ->assertOk()
            ->assertSee("const modeStorageKey = 'home_hero_panel_mode';", false)
            ->assertSee("localStorage.getItem(modeStorageKey)", false)
            ->assertSee("setMode(savedMode === 'live' ? 'live' : 'damo');", false);
    }

    public function test_my_center_renders_english_ui_copy_when_locale_is_en(): void
    {
        $this->get('/me?locale=en')
            ->assertOk()
            ->assertSee('My Center | '.config('app.name'))
            ->assertSee('Deposit & Withdrawal')
            ->assertSee('Please choose a deposit and withdrawal method.')
            ->assertSee('Crypto Deposit')
            ->assertSee('Bank Card')
            ->assertSee('Next')
            ->assertSee('Account Info')
            ->assertSee('Temporary Account')
            ->assertSee('Guest Not Registered')
            ->assertSee('Account Status')
            ->assertSee('Created At')
            ->assertSee('Copy Account')
            ->assertSee('Set Trading PIN')
            ->assertSee('Please enter and confirm a 6-digit trading PIN.')
            ->assertSee('Enter 6-digit PIN')
            ->assertSee('Confirm 6-digit PIN')
            ->assertSee('Confirm Registration');
    }

    public function test_my_center_shows_mnemonic_setup_notice_when_user_has_no_mnemonic(): void
    {
        $user = User::factory()->create([
            'mnemonic_lookup' => null,
        ]);

        $this->actingAs($user)
            ->get('/me')
            ->assertOk()
            ->assertSee('你还没有生成助记词，请先生成并妥善保存。');
    }

    public function test_my_center_hides_mnemonic_setup_notice_when_user_has_mnemonic(): void
    {
        $user = User::factory()->create([
            'mnemonic_lookup' => hash('sha256', 'apple book cat dog egg fish game hand ice jump'),
        ]);

        $this->actingAs($user)
            ->get('/me')
            ->assertOk()
            ->assertDontSee('你还没有生成助记词，请先生成并妥善保存。');
    }
}
