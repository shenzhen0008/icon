<?php

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamChatAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_stream_chat_agent_page(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/stream-chat-agent')
            ->assertOk()
            ->assertSee('agent-chat-file')
            ->assertSee('agent-chat-sound-prompt')
            ->assertSee('agent-mobile-list-view')
            ->assertSee('agent-mobile-chat-view')
            ->assertSee('agent-mobile-back')
            ->assertSee('agent-open-channel-drawer')
            ->assertSee('agent-channel-drawer');
    }

    public function test_guest_cannot_access_stream_chat_agent_routes(): void
    {
        $this->get('/stream-chat-agent')->assertRedirect('/login');
        $this->post('/stream-chat-agent/token')->assertRedirect('/login');
    }

    public function test_stream_chat_agent_token_endpoint_returns_payload_for_authenticated_user(): void
    {
        config()->set('stream_chat.api_key', 'test-key');
        config()->set('stream_chat.api_secret', 'test-secret');

        $user = User::factory()->create([
            'name' => 'Support One',
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

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/stream-chat-agent/token')
            ->assertStatus(503)
            ->assertJson([
                'message' => 'Stream Chat is not configured.',
            ]);
    }
}
