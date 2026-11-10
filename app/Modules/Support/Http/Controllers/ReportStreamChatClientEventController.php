<?php

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReportStreamChatClientEventController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'trace_id' => ['required', 'uuid'],
            'stage' => ['required', 'string', Rule::in([
                'sdk_import', 'token_request', 'token_response', 'token_json',
                'connect_user', 'watch_channel', 'history_state_missing', 'render_history',
                'send_message', 'send_attachment',
            ])],
            'error_name' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:300'],
            'http_status' => ['nullable', 'integer', 'between:100,599'],
            'stream_code' => ['nullable', 'string', 'max:64'],
            'api_path' => ['nullable', 'string', 'max:150', 'regex:/^\/api\/v[0-9]+\/[A-Za-z0-9_\/-]*$/'],
            'watch_messages_array' => ['nullable', 'boolean'],
            'state_messages_array' => ['nullable', 'boolean'],
            'state_messages_count' => ['nullable', 'integer', 'between:0,1000'],
        ]);

        $payload['message'] = isset($payload['message'])
            ? preg_replace('/[\r\n\x00-\x1F\x7F]+/', ' ', $payload['message'])
            : null;

        Log::warning('stream_chat_client_event', [
            'event' => $payload,
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json(['ok' => true], 202);
    }
}
