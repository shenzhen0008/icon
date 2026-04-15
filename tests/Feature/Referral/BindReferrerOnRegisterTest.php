<?php

namespace Tests\Feature\Referral;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BindReferrerOnRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_link_is_captured_in_session_cookie_and_register_form(): void
    {
        User::factory()->create([
            'invite_code' => 'ABC123',
        ]);

        $response = $this->get('/?invite_code=ABC123');

        $response->assertOk()
            ->assertSessionHas('referral_invite_code', 'ABC123')
            ->assertCookie('referral_invite_code')
            ->assertSee('name="invite_code"', false)
            ->assertSee('value="ABC123"', false);
    }

    public function test_registering_with_valid_invite_code_binds_referrer_and_generates_own_code(): void
    {
        $referrer = User::factory()->create([
            'invite_code' => 'REF123',
        ]);

        $this->get('/')->assertOk();
        $temporaryUsername = session('temp_username');

        $this->post('/register', [
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'invite_code' => 'REF123',
        ])->assertRedirect('/me');

        $user = User::query()->where('username', $temporaryUsername)->firstOrFail();

        $this->assertSame($referrer->id, $user->referrer_id);
        $this->assertNotNull($user->invite_code);
        $this->assertNotSame('REF123', $user->invite_code);
        $this->assertTrue(Hash::check('password1234', $user->password));
    }

    public function test_invalid_invite_code_does_not_bind_referrer(): void
    {
        $this->get('/')->assertOk();
        $temporaryUsername = session('temp_username');

        $this->post('/register', [
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'invite_code' => 'MISSING',
        ])->assertRedirect('/me');

        $user = User::query()->where('username', $temporaryUsername)->firstOrFail();

        $this->assertNull($user->referrer_id);
        $this->assertNotNull($user->invite_code);
    }

    public function test_session_invite_code_is_used_when_form_value_is_empty(): void
    {
        $referrer = User::factory()->create([
            'invite_code' => 'SESSION1',
        ]);

        $this->get('/?invite_code=SESSION1')->assertOk();
        $temporaryUsername = session('temp_username');

        $this->post('/register', [
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'invite_code' => '',
        ])->assertRedirect('/me');

        $user = User::query()->where('username', $temporaryUsername)->firstOrFail();

        $this->assertSame($referrer->id, $user->referrer_id);
        $this->assertNull(session('referral_invite_code'));
    }

    public function test_signed_cookie_invite_code_is_used_when_session_and_form_are_empty(): void
    {
        $referrer = User::factory()->create([
            'invite_code' => 'COOKIE1',
        ]);

        $this->get('/')->assertOk();
        $temporaryUsername = session('temp_username');
        session()->forget('referral_invite_code');
        $cookieValue = app(\App\Modules\Referral\Support\InviteCodeCookieSigner::class)->sign('COOKIE1');

        $this->withUnencryptedCookie('referral_invite_code', $cookieValue)
            ->get('/register')
            ->assertOk()
            ->assertSee('value="COOKIE1"', false);

        session()->forget('referral_invite_code');

        $this->withUnencryptedCookie('referral_invite_code', $cookieValue)
            ->post('/register', [
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
                'invite_code' => '',
            ])->assertRedirect('/me');

        $user = User::query()->where('username', $temporaryUsername)->firstOrFail();

        $this->assertSame($referrer->id, $user->referrer_id);
    }

    public function test_authenticated_user_visiting_invite_link_does_not_change_referrer(): void
    {
        $originalReferrer = User::factory()->create([
            'invite_code' => 'ORIGIN1',
        ]);
        $otherReferrer = User::factory()->create([
            'invite_code' => 'OTHER1',
        ]);
        $user = User::factory()->create([
            'referrer_id' => $originalReferrer->id,
            'invite_code' => 'USER01',
        ]);

        $this->actingAs($user)
            ->get('/?invite_code=OTHER1')
            ->assertOk();

        $user->refresh();

        $this->assertSame($originalReferrer->id, $user->referrer_id);
        $this->assertFalse(session()->has('referral_invite_code'));
        $this->assertNotSame($otherReferrer->id, $user->referrer_id);
    }
}
