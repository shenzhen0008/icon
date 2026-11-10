<?php

namespace App\Modules\Support\Services;

use App\Models\User;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class StreamChatTokenService
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
    ) {}

    public function issueGuestTokenPayload(Request $request): array
    {
        return $this->issueCustomerTokenPayload($request, true);
    }

    public function issueExistingGuestTokenPayload(Request $request): array
    {
        return $this->issueCustomerTokenPayload($request, false);
    }

    private function issueCustomerTokenPayload(Request $request, bool $allowGuestCreation): array
    {
        $apiKey = (string) config('stream_chat.api_key');
        $apiSecret = (string) config('stream_chat.api_secret');

        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Stream Chat is not configured.');
        }

        $user = $request->user();
        if ($user instanceof User) {
            $streamUserId = (string) $user->username;
        } else {
            $streamUserId = (string) $request->session()->get('stream_chat.guest_id');
            if ($streamUserId === '') {
                if (! $allowGuestCreation) {
                    throw new RuntimeException('Stream Chat guest session is not initialized.');
                }

                $streamUserId = 'guest_'.Str::lower(Str::random(16));
                $request->session()->put('stream_chat.guest_id', $streamUserId);
            }
        }

        $displayName = $this->resolveDisplayName($request);
        if (! $user instanceof User) {
            $request->session()->put('stream_chat.guest_name', $displayName);
        }

        $channelType = (string) config('stream_chat.channel_type', 'messaging');
        $channelPrefix = (string) config('stream_chat.channel_prefix', 'support');
        $channelId = $user instanceof User
            ? sprintf('%s-customer-%s', $channelPrefix, $streamUserId)
            : sprintf('%s-%s', $channelPrefix, str_replace('_', '-', $streamUserId));
        $agentUserId = (string) config('stream_chat.agent_user_id', 'support_agent_1');

        return [
            'apiKey' => $apiKey,
            'token' => $this->createUserToken($streamUserId, $apiSecret),
            'user' => [
                'id' => $streamUserId,
                'name' => $displayName,
            ],
            'channel' => [
                'type' => $channelType,
                'id' => $channelId,
                'name' => $displayName,
                'members' => [$streamUserId, $agentUserId],
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

    private function resolveDisplayName(Request $request): string
    {
        $user = $request->user();
        if ($user instanceof User) {
            $username = trim((string) $user->username);
            if ($username !== '') {
                return $username;
            }
        }

        $temporaryUsername = $this->temporaryAccountService->ensureGuestTempUsername($request);
        if (is_string($temporaryUsername) && $temporaryUsername !== '') {
            return $temporaryUsername;
        }

        $legacyName = trim((string) $request->session()->get('stream_chat.guest_name', ''));
        if ($legacyName !== '' && ! str_starts_with($legacyName, 'Guest-')) {
            return $legacyName;
        }

        return 'guest';
    }
}
