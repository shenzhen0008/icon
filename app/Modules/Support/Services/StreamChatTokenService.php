<?php

namespace App\Modules\Support\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class StreamChatTokenService
{
    public function issueGuestTokenPayload(Request $request): array
    {
        $apiKey = (string) config('stream_chat.api_key');
        $apiSecret = (string) config('stream_chat.api_secret');

        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Stream Chat is not configured.');
        }

        $guestId = (string) $request->session()->get('stream_chat.guest_id');
        if ($guestId === '') {
            $guestId = 'guest_'.Str::lower(Str::random(16));
            $request->session()->put('stream_chat.guest_id', $guestId);
        }

        $guestName = (string) $request->session()->get('stream_chat.guest_name');
        if ($guestName === '') {
            $guestName = 'Guest-'.Str::upper(Str::random(4));
            $request->session()->put('stream_chat.guest_name', $guestName);
        }

        $channelType = (string) config('stream_chat.channel_type', 'messaging');
        $channelPrefix = (string) config('stream_chat.channel_prefix', 'support');
        $channelId = sprintf('%s-%s', $channelPrefix, str_replace('_', '-', $guestId));
        $agentUserId = (string) config('stream_chat.agent_user_id', 'support_agent_1');

        return [
            'apiKey' => $apiKey,
            'token' => $this->createUserToken($guestId, $apiSecret),
            'user' => [
                'id' => $guestId,
                'name' => $guestName,
            ],
            'channel' => [
                'type' => $channelType,
                'id' => $channelId,
                'name' => 'Support '.$guestName,
                'members' => [$guestId, $agentUserId],
            ],
        ];
    }

    public function issueExistingGuestTokenPayload(Request $request): array
    {
        $apiKey = (string) config('stream_chat.api_key');
        $apiSecret = (string) config('stream_chat.api_secret');

        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Stream Chat is not configured.');
        }

        $guestId = (string) $request->session()->get('stream_chat.guest_id');
        if ($guestId === '') {
            throw new RuntimeException('Stream Chat guest session is not initialized.');
        }

        $guestName = (string) $request->session()->get('stream_chat.guest_name');
        if ($guestName === '') {
            $guestName = 'Guest';
        }

        $channelType = (string) config('stream_chat.channel_type', 'messaging');
        $channelPrefix = (string) config('stream_chat.channel_prefix', 'support');
        $channelId = sprintf('%s-%s', $channelPrefix, str_replace('_', '-', $guestId));
        $agentUserId = (string) config('stream_chat.agent_user_id', 'support_agent_1');

        return [
            'apiKey' => $apiKey,
            'token' => $this->createUserToken($guestId, $apiSecret),
            'user' => [
                'id' => $guestId,
                'name' => $guestName,
            ],
            'channel' => [
                'type' => $channelType,
                'id' => $channelId,
                'name' => 'Support '.$guestName,
                'members' => [$guestId, $agentUserId],
            ],
        ];
    }

    public function issueAgentTokenPayload(User $user): array
    {
        $apiKey = (string) config('stream_chat.api_key');
        $apiSecret = (string) config('stream_chat.api_secret');

        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Stream Chat is not configured.');
        }

        $agentId = (string) config('stream_chat.agent_user_id', 'support_agent_1');
        $agentName = (string) ($user->name ?: $user->username ?: 'Support Agent');

        return [
            'apiKey' => $apiKey,
            'token' => $this->createUserToken($agentId, $apiSecret),
            'user' => [
                'id' => $agentId,
                'name' => $agentName,
            ],
            'channel' => [
                'type' => (string) config('stream_chat.channel_type', 'messaging'),
                'prefix' => (string) config('stream_chat.channel_prefix', 'support'),
            ],
        ];
    }

    private function createUserToken(string $userId, string $secret): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + 86400,
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $secret, true);

        return $encodedHeader.'.'.$encodedPayload.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
