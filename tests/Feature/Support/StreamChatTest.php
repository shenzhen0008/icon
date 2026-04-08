<?php

namespace Tests\Feature\Support;

use Tests\TestCase;

class StreamChatTest extends TestCase
{
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
            ->assertSee('stream-chat-sound-prompt');
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

        $this->withSession([
            'stream_chat.guest_id' => 'guest_test1234',
            'stream_chat.guest_name' => 'Guest-ABCD',
        ])->get('/stream-chat/notify-token')
            ->assertOk()
            ->assertJsonStructure([
                'apiKey',
                'token',
                'user' => ['id', 'name'],
                'channel' => ['type', 'id', 'name', 'members'],
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
            ]);
    }
}
