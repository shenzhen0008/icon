<?php

namespace Tests\Feature\Support;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StreamChatClientEventTest extends TestCase
{
    public function test_client_diagnostic_event_is_logged_without_authentication(): void
    {
        Log::spy();

        $this->postJson('/stream-chat/client-events', [
            'trace_id' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            'stage' => 'send_message',
            'error_name' => 'AxiosError',
            'message' => 'timeout of 3000ms exceeded',
            'http_status' => null,
            'stream_code' => 'ECONNABORTED',
            'api_path' => '/api/v2/chat/channels',
        ])->assertStatus(202)->assertJson(['ok' => true]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('stream_chat_client_event', \Mockery::on(fn (array $context): bool =>
                $context['event']['stage'] === 'send_message'
                && $context['event']['api_path'] === '/api/v2/chat/channels'
            ));
    }

    public function test_client_diagnostic_rejects_unapproved_stage_and_api_path(): void
    {
        $this->postJson('/stream-chat/client-events', [
            'trace_id' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            'stage' => 'unexpected',
            'api_path' => 'https://example.com/?token=secret',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['stage', 'api_path']);
    }
}
