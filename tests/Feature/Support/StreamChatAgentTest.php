<?php

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamChatAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_stream_chat_agent_page(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($user)
            ->get('/stream-chat-agent')
            ->assertOk()
            ->assertSee('flex min-w-0 items-center gap-2')
            ->assertSee('flex-1 min-w-0 rounded-lg border border-theme')
            ->assertSee('data-agent-unread-dot')
            ->assertSee('data-agent-hide-channel')
            ->assertSee('channel.hide()')
            ->assertSee('notification.message_new')
            ->assertSee("notification.message_new', (event) => {", false)
            ->assertSee('isIncomingCustomerMessage(event.message)', false)
            ->assertSee('event.user?.id', false)
            ->assertSee('normalizeChannelId', false)
            ->assertSee('refreshChannels')
            ->assertSee('agent-chat-file')
            ->assertSee('agent-chat-sound-prompt')
            ->assertSee('agent-mobile-list-view')
            ->assertSee('agent-mobile-chat-view')
            ->assertSee('agent-mobile-back')
            ->assertSee('agent-open-channel-drawer')
            ->assertSee('agent-channel-drawer')
            ->assertSee('agent-user-context-menu')
            ->assertSee('agent-copy-username-button')
            ->assertSee('/admin/users?search=', false);
    }

    public function test_guest_cannot_access_stream_chat_agent_routes(): void
    {
        $this->get('/stream-chat-agent')->assertRedirect('/login');
        $this->post('/stream-chat-agent/token')->assertRedirect('/login');
    }

    public function test_stream_chat_agent_token_endpoint_returns_payload_for_admin_user(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $user = User::factory()->create([
            'name' => 'Support One',
            'is_admin' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/stream-chat-agent/token')
            ->assertOk()
            ->assertJsonStructure([
                'apiKey',
                'token',
                'user' => ['id', 'name'],
                'channel' => ['type', 'prefix'],
            ])
            ->assertJson([
                'apiKey' => 'test-key',
                'user' => ['name' => 'Support One'],
            ]);
    }

    public function test_stream_chat_agent_token_endpoint_returns_503_when_not_configured(): void
    {
        config()->set('stream_chat.api_key', null);
        config()->set('stream_chat.api_secret', null);

        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/stream-chat-agent/token')
            ->assertStatus(503)
            ->assertJson([
                'message' => 'Stream Chat is not configured.',
            ]);
    }

    public function test_non_admin_user_cannot_access_stream_chat_agent_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/stream-chat-agent')
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson('/stream-chat-agent/token')
            ->assertForbidden();
    }
}
