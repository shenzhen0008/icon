<?php

namespace App\Modules\ClientEnv\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ClientEnv\Http\Requests\CollectClientEnvRequest;
use App\Modules\ClientEnv\Services\ClientEnvDetectorService;
use App\Modules\ClientEnv\Services\ClientEnvProbeLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CollectClientEnvController extends Controller
{
    public function __construct(
        private readonly ClientEnvDetectorService $detectorService,
        private readonly ClientEnvProbeLogService $probeLogService,
    ) {
    }

    public function __invoke(CollectClientEnvRequest $request): JsonResponse
    {
        if (!(bool) config('client_env.enabled', true)) {
            abort(403);
        }

        $serverDetect = $this->detectorService->detect($request);
        $userKey = $this->resolveUserKey($request->ip());
        $uniqueKey = $this->buildUniqueKey($userKey, (string) $request->userAgent(), $serverDetect);

        $entry = [
            'timestamp' => now()->toIso8601String(),
            'unique_key' => $uniqueKey,
            'request_id' => (string) ($request->headers->get('X-Request-Id') ?: Str::uuid()),
            'user_key' => $userKey,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'server_detect' => $serverDetect,
            'client_reported' => $request->validated('client'),
        ];

        $saved = $this->probeLogService->appendUnique($entry);

        return response()->json([
            'ok' => true,
            'saved' => $saved,
            'duplicate' => !$saved,
            'path' => (string) config('client_env.log_path', 'client-env/probe-log.jsonl'),
            'entry' => [
                'timestamp' => $entry['timestamp'],
                'request_id' => $entry['request_id'],
                'unique_key' => $entry['unique_key'],
            ],
        ]);
    }

    private function resolveUserKey(?string $ip): string
    {
        $userId = Auth::id();
        if ($userId !== null) {
            return 'user:'.$userId;
        }

        return 'ip:'.($ip ?: 'unknown');
    }

    /**
     * @param array<string, mixed> $serverDetect
     */
    private function buildUniqueKey(string $userKey, string $userAgent, array $serverDetect): string
    {
        $browser = (array) ($serverDetect['browser'] ?? []);
        $os = (array) ($serverDetect['os'] ?? []);
        $uaHash = sha1($userAgent);

        $parts = [
            $userKey,
            $uaHash,
            (string) ($serverDetect['device_type'] ?? 'unknown'),
            (string) ($serverDetect['is_webview'] ?? false),
            (string) ($browser['name'] ?? 'unknown'),
            (string) ($browser['version'] ?? 'unknown'),
            (string) ($os['name'] ?? 'unknown'),
            (string) ($os['version'] ?? 'unknown'),
        ];

        return sha1(implode('|', $parts));
    }
}
