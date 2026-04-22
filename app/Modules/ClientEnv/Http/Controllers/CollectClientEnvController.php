<?php

namespace App\Modules\ClientEnv\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ClientEnv\Http\Requests\CollectClientEnvRequest;
use App\Modules\ClientEnv\Services\ClientEnvDetectorService;
use App\Modules\ClientEnv\Services\ClientEnvProbeLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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

        $entry = [
            'timestamp' => now()->toIso8601String(),
            'request_id' => (string) ($request->headers->get('X-Request-Id') ?: Str::uuid()),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'server_detect' => $this->detectorService->detect($request),
            'client_reported' => $request->validated('client'),
        ];

        $this->probeLogService->append($entry);

        return response()->json([
            'ok' => true,
            'saved' => true,
            'path' => (string) config('client_env.log_path', 'client-env/probe-log.jsonl'),
            'entry' => [
                'timestamp' => $entry['timestamp'],
                'request_id' => $entry['request_id'],
            ],
        ]);
    }
}
