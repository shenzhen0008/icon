<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visit_assigns_temp_username_in_session(): void
    {
        $this->get('/')->assertOk();

        $this->assertTrue(session()->has('temp_username'));

        $tempUsername = session('temp_username');
        $this->assertIsString($tempUsername);
        $this->assertSame(21, strlen($tempUsername));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{21}$/', $tempUsername);
    }

    public function test_guest_can_activate_account_by_setting_password(): void
    {
        $this->get('/')->assertOk();
        $tempUsername = session('temp_username');

        $response = $this->post('/register', [
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect('/me');
        $this->assertAuthenticated();

        $user = User::query()->where('username', $tempUsername)->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('123456', $user->password));
        $this->assertNull(session('temp_username'));
    }

    public function test_registered_user_can_be_restored_from_remember_cookie_without_session(): void
    {
        $this->get('/')->assertOk();

        $response = $this->post('/register', [
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect('/me');

        $rememberCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => Str::startsWith($cookie->getName(), 'remember_web_'));

        $this->assertNotNull($rememberCookie);

        $this->flushSession();

        $this->withCookie($rememberCookie->getName(), $rememberCookie->getValue())
            ->get('/')
            ->assertOk();

        $this->assertAuthenticated();
        $this->assertFalse(session()->has('temp_username'));
    }

    public function test_activation_without_temp_username_is_forbidden(): void
    {
        $this->post('/register', [
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertForbidden();
    }

    public function test_activation_rejects_invalid_password_input(): void
    {
        $this->get('/')->assertOk();

        $this->from('/register')->post('/register', [
            'password' => '12ab56',
            'password_confirmation' => '12ab56',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['password']);
    }

    public function test_activation_is_rate_limited_after_too_many_failed_pin_attempts(): void
    {
        $this->get('/')->assertOk();

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $this->from('/register')->post('/register', [
                'password' => '12ab56',
                'password_confirmation' => '12ab56',
            ])->assertRedirect('/register');
        }

        $this->post('/register', [
            'password' => '12ab56',
            'password_confirmation' => '12ab56',
        ])->assertStatus(429);
    }

    public function test_user_can_login_with_username_and_remember_me(): void
    {
        $user = User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'password' => bcrypt('password1234'),
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password1234',
            'remember' => '1',
        ]);

        $response->assertRedirect('/me');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'password' => bcrypt('password1234'),
        ]);

        $this->from('/login')->post('/login', [
            'username' => 'AbC123xYz987QwErT654X',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_user_can_login_with_short_username(): void
    {
        $user = User::factory()->create([
            'username' => 'aaa123',
            'password' => bcrypt('aaa123456'),
        ]);

        $this->post('/login', [
            'username' => 'aaa123',
            'password' => 'aaa123456',
        ])->assertRedirect('/me');

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_confirmation_is_required_for_sensitive_page(): void
    {
        $user = User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'password' => bcrypt('password1234'),
        ]);

        $this->actingAs($user)
            ->get('/sensitive')
            ->assertRedirect('/confirm-password');

        $this->actingAs($user)
            ->post('/confirm-password', ['password' => 'password1234'])
            ->assertRedirect();

        $this->actingAs($user)
            ->get('/sensitive')
            ->assertOk();
    }

    public function test_logout_clears_authentication_state(): void
    {
        $user = User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'password' => bcrypt('password1234'),
        ]);

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_logout_then_revisit_my_shows_guest_state(): void
    {
        $user = User::factory()->create([
            'username' => 'AbC123xYz987QwErT654X',
            'password' => bcrypt('password1234'),
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password1234',
            'remember' => '1',
        ])->assertRedirect('/me');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->get('/me')
            ->assertOk()
            ->assertSee('临时账号')
            ->assertSee('访客未注册')
            ->assertDontSee($user->username);
    }

    public function test_login_page_localizes_fixed_ui_copy_for_english(): void
    {
        $this->get('/login?locale=en')
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('Username')
            ->assertSee('Password')
            ->assertSee('Remember me');
    }
}
