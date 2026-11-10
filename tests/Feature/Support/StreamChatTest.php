<?php

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_chat_page_is_accessible_for_guest(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $this->get('/stream-chat')
            ->assertOk()
            ->assertSee('data-theme="', false)
            ->assertSee('user-scalable=no')
            ->assertSee('bg-theme text-theme')
            ->assertSee('top: var(--top-nav-height, 4.25rem);', false)
            ->assertSee('var(--chat-keyboard-inset, 0px)', false)
            ->assertSee('--chat-keyboard-inset')
            ->assertSee('visualViewport')
            ->assertSee('flex min-w-0 items-center gap-2')
            ->assertSee('flex-1 min-w-0 rounded-lg border border-theme')
            ->assertSee('stream-chat-file')
            ->assertSee('stream-chat-sound-prompt')
            ->assertSee('await channel.watch();', false)
            ->assertSee('stream-chat@9.55.0/+esm')
            ->assertSee('await channel.sendMessage({ text, attachments: [] });', false)
            ->assertSee("localStorage.setItem('stream_chat_has_session', '1');", false)
            ->assertSee('channel.markRead().catch(() => {});', false)
            ->assertDontSee('/privacy', false)
            ->assertDontSee('/terms', false)
            ->assertDontSee('stream_chat_notify_bootstrap_ready', false)
            ->assertDontSee("localStorage.setItem(streamNotifyBootstrapKey, '1');", false);
    }

    public function test_stream_chat_page_renders_english_ui_copy_when_locale_is_en(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $this->get('/stream-chat?locale=en')
            ->assertOk()
            ->assertSee('Online Support | '.config('app.name'))
            ->assertSee('Connecting to support...')
            ->assertSee('Type your message...')
            ->assertSee('Enable message alert sound? A tone will play when new messages arrive.')
            ->assertSee('Enable');
    }

    public function test_guest_can_get_stream_chat_token_payload(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $response = $this->postJson('/stream-chat/guest-token');

        $response->assertOk()
            ->assertJsonStructure([
                'apiKey',
                'token',
                'user' => ['id', 'name'],
                'channel' => ['type', 'id', 'name', 'members'],
            ]);

        $this->assertSame('test-key', $response->json('apiKey'));
    }

    public function test_guest_token_prefers_temp_username_for_display_name(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');
        $tempUsernameKey = (string) config('user.temp_username_session_key');

        $this->withSession([
            $tempUsernameKey => 'aaa123',
        ])->postJson('/stream-chat/guest-token')
            ->assertOk()
            ->assertJson([
                'user' => ['name' => 'aaa123'],
                'channel' => ['name' => 'aaa123'],
            ]);
    }

    public function test_guest_token_endpoint_returns_503_when_stream_chat_is_not_configured(): void
    {
        config()->set('stream_chat.api_key', null);
        config()->set('stream_chat.api_secret', null);

        $this->postJson('/stream-chat/guest-token')
            ->assertStatus(503)
            ->assertJson([
                'message' => 'Stream Chat is not configured.',
            ]);
    }

    public function test_notify_token_returns_payload_when_guest_session_exists(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');
        $tempUsernameKey = (string) config('user.temp_username_session_key');

        $this->withSession([
            'stream_chat.guest_id' => 'guest_test1234',
            $tempUsernameKey => 'aaa123',
        ])->get('/stream-chat/notify-token')
            ->assertOk()
            ->assertJsonStructure([
                'apiKey',
                'token',
                'user' => ['id', 'name'],
                'channel' => ['type', 'id', 'name', 'members'],
            ])
            ->assertJson([
                'user' => ['name' => 'aaa123'],
                'channel' => ['name' => 'aaa123'],
            ]);
    }

    public function test_notify_token_returns_404_without_guest_session(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $this->get('/stream-chat/notify-token')
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Stream Chat guest session is not initialized.',
            ])
            ->assertSessionMissing('stream_chat.guest_id');
    }

    public function test_customer_identity_is_stable_across_sessions_and_token_endpoints(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');
        config()->set('stream_chat.channel_prefix', 'help');
        $user = User::factory()->create(['username' => 'CustomerAbC123']);

        $first = $this->actingAs($user)->getJson('/stream-chat/notify-token')
            ->assertOk()->assertSessionMissing('stream_chat.guest_id');
        $this->app['session']->flush();
        $this->app['session']->regenerate();

        foreach (['/stream-chat/guest-token', '/stream-chat/notify-token'] as $endpoint) {
            $response = $endpoint === '/stream-chat/guest-token'
                ? $this->postJson($endpoint)
                : $this->getJson($endpoint);
            $response->assertOk()->assertSessionMissing('stream_chat.guest_id');
            $this->assertSame($first->json('user'), $response->json('user'));
            $this->assertSame($first->json('channel'), $response->json('channel'));
            $this->assertSame('CustomerAbC123', $response->json('user.id'));
            $this->assertSame('help-customer-CustomerAbC123', $response->json('channel.id'));
            $this->assertSame([
                'CustomerAbC123', config('stream_chat.agent_user_id'),
            ], $response->json('channel.members'));

            [$header, $payload, $signature] = explode('.', $response->json('token'));
            $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($response->json('user.id'), $claims['user_id']);
            $this->assertSame(86400, $claims['exp'] - $claims['iat']);
            $expectedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', $header.'.'.$payload, 'test-secret', true)), '+/', '-_'), '=');
            $this->assertSame($expectedSignature, $signature);
        }
    }

    public function test_customer_identity_ignores_guest_session_and_spoofed_input(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');
        $alice = User::factory()->create(['username' => 'Alice123']);
        $bob = User::factory()->create(['username' => 'Bob123']);
        $alicePayload = $this->actingAs($alice)->postJson('/stream-chat/guest-token')->assertOk()->json();

        $input = [
            'username' => $alice->username,
            'user_id' => $alice->username,
            'channel' => $alicePayload['channel'],
            'members' => [$alice->username],
        ];
        $this->actingAs($bob)->withSession(['stream_chat.guest_id' => 'guest_old']);

        foreach ([
            $this->postJson('/stream-chat/guest-token', $input),
            $this->getJson('/stream-chat/notify-token?'.http_build_query($input)),
        ] as $response) {
            $response->assertOk()->assertJsonPath('user.id', 'Bob123')
                ->assertJsonPath('channel.id', 'support-customer-Bob123')
                ->assertSessionHas('stream_chat.guest_id', 'guest_old');
            $this->assertNotSame($alicePayload['channel']['id'], $response->json('channel.id'));
            $this->assertNotContains('Alice123', $response->json('channel.members'));
        }
    }

    public function test_guest_identity_remains_stable_and_uses_existing_channel_format(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');
        config()->set('stream_chat.channel_prefix', 'help');
        $first = $this->postJson('/stream-chat/guest-token')->assertOk();
        $guestId = $first->json('user.id');
        $this->assertMatchesRegularExpression('/^guest_[a-z0-9]{16}$/', $guestId);

        $this->postJson('/stream-chat/guest-token')->assertOk()->assertJsonPath('user.id', $guestId);
        $this->getJson('/stream-chat/notify-token')->assertOk()
            ->assertJsonPath('user.id', $guestId)
            ->assertJsonPath('channel.id', 'help-'.str_replace('_', '-', $guestId));
    }

    public function test_notify_token_preserves_configuration_error_response(): void
    {
        config()->set('stream_chat.api_key', null);
        config()->set('stream_chat.api_secret', null);

        $this->getJson('/stream-chat/notify-token')->assertStatus(404)
            ->assertJsonPath('message', 'Stream Chat is not configured.')
            ->assertSessionMissing('stream_chat.guest_id');
    }
}
