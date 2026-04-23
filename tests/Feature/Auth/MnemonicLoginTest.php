<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MnemonicLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_mnemonic_phrase(): void
    {
        $phrase = 'apple book cat dog egg fish game hand ice jump';

        $user = User::factory()->create([
            'mnemonic_lookup' => hash('sha256', $phrase),
        ]);

        $response = $this->post('/login/mnemonic', [
            'mnemonic_phrase' => $phrase,
            'remember' => '1',
        ]);

        $response->assertRedirect('/me');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_mnemonic_login_rejects_phrase_with_invalid_word_count(): void
    {
        $this->from('/login')->post('/login/mnemonic', [
            'mnemonic_phrase' => 'apple book cat',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('mnemonic_phrase');
    }

    public function test_mnemonic_login_rejects_phrase_with_word_outside_wordlist(): void
    {
        $this->from('/login')->post('/login/mnemonic', [
            'mnemonic_phrase' => 'apple book cat dog egg fish game hand ice unknown',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('mnemonic_phrase');
    }

    public function test_mnemonic_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'mnemonic_lookup' => hash('sha256', 'apple book cat dog egg fish game hand ice jump'),
        ]);

        $this->from('/login')->post('/login/mnemonic', [
            'mnemonic_phrase' => 'apple book cat dog egg fish game hand ice king',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('mnemonic_phrase');

        $this->assertGuest();
    }

    public function test_guest_cannot_regenerate_mnemonic_phrase(): void
    {
        $this->post('/me/mnemonic/regenerate')->assertRedirect('/login');
    }

    public function test_guest_cannot_access_mnemonic_page(): void
    {
        $this->get('/me/mnemonic')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_regenerate_mnemonic_phrase(): void
    {
        $user = User::factory()->create([
            'mnemonic_lookup' => null,
        ]);

        $response = $this->actingAs($user)->from('/me')->post('/me/mnemonic/regenerate');

        $response->assertRedirect('/me/mnemonic');
        $response->assertSessionHas('generated_mnemonic_phrase');

        $phrase = session('generated_mnemonic_phrase');
        $this->assertIsString($phrase);

        $user->refresh();
        $this->assertNotNull($user->mnemonic_lookup);
        $this->assertSame(hash('sha256', $phrase), $user->mnemonic_lookup);
    }

    public function test_authenticated_user_can_access_mnemonic_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/me/mnemonic')
            ->assertOk()
            ->assertSee(__('pages/me.account.mnemonic_title'));
    }
}
