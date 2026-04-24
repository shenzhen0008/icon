<?php

namespace App\Modules\ClientEnv\Http\Middleware;

use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use App\Modules\ClientEnv\Services\ClientEnvDetectorService;
use App\Modules\ClientEnv\Services\ClientEnvDecisionAuditService;
use App\Modules\ClientEnv\Services\ClientEnvDecisionService;
use App\Modules\ClientEnv\Services\ClientEnvProbeLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ClientEnvProbeMiddleware
{
    public function __construct(
        private readonly ClientEnvDetectorService $detectorService,
        private readonly ClientEnvDecisionService $decisionService,
        private readonly ClientEnvDecisionAuditService $decisionAuditService,
        private readonly ClientEnvProbeLogService $probeLogService,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldProbe($request)) {
            $entry = $this->buildEntry($request);
            $attributeKey = (string) config('client_env.middleware.attribute_key', 'client_env_probe');
            $request->attributes->set($attributeKey, $entry);

            $decision = $this->isDecisionExcludedPath($request)
                ? $this->buildSkippedDecision()
                : $this->resolveDecision($entry);
            $decisionAttributeKey = (string) config('client_env.decision.attribute_key', 'client_env_decision');
            $request->attributes->set($decisionAttributeKey, $decision);

            if ((bool) config('client_env.middleware.persist', true)) {
                try {
                    $this->probeLogService->appendUnique($entry);
                } catch (Throwable) {
                    // Probe persistence should not block regular request handling.
                }
            }

            if (! $this->isDecisionExcludedPath($request)) {
                try {
                    $this->decisionAuditService->audit($request, $entry, $decision);
                } catch (Throwable) {
                    // Decision audit should not block regular request handling.
                }
            }

            if ($this->shouldDenyRequest($request, $decision)) {
                return $this->buildDeniedResponse($request, $decision);
            }
        }

        return $next($request);
    }

    private function shouldProbe(Request $request): bool
    {
        if (!(bool) config('client_env.middleware.enabled', true)) {
            return false;
        }

        $path = ltrim($request->path(), '/');
        $excludedPaths = (array) config('client_env.middleware.excluded_paths', []);

        foreach ($excludedPaths as $pattern) {
            if ($pattern !== null && Str::is((string) $pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function resolveDecision(array $entry): array
    {
        if (! $this->isDecisionFeatureEnabled()) {
            return [
                'decision' => 'allow',
                'reason_code' => 'decision_disabled',
                'risk_score' => 0,
                'rule_version' => (string) config('client_env.decision.rule_version', 'v1'),
            ];
        }

        return $this->decisionService->decide($entry);
    }

    /**
     * @param array<string, mixed> $decision
     */
    private function shouldDenyRequest(Request $request, array $decision): bool
    {
        if ((string) ($decision['decision'] ?? 'allow') !== 'deny') {
            return false;
        }

        if ($this->isDecisionExcludedPath($request)) {
            return false;
        }

        $mode = strtolower((string) config('client_env.decision.mode', 'shadow'));
        if ($mode !== 'enforce') {
            return false;
        }

        $path = ltrim($request->path(), '/');
        $patterns = (array) config('client_env.decision.enforce_paths', []);
        if ($patterns === []) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if ($pattern !== null && Str::is((string) $pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function isDecisionExcludedPath(Request $request): bool
    {
        $path = ltrim($request->path(), '/');
        $patterns = (array) config('client_env.decision.excluded_paths', []);
        foreach ($patterns as $pattern) {
            if ($pattern !== null && Str::is((string) $pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function isDecisionFeatureEnabled(): bool
    {
        if (!(bool) config('client_env.decision.enabled', true)) {
            return false;
        }

        try {
            if (!Schema::hasTable('client_env_decision_settings')) {
                return true;
            }

            $setting = ClientEnvDecisionSetting::query()->find(1);
            if ($setting === null) {
                // Backward compatibility for historical dirty data before singleton enforcement.
                $setting = ClientEnvDecisionSetting::query()->orderByDesc('id')->first();
            }
            if ($setting === null) {
                return true;
            }

            return (bool) $setting->is_enabled;
        } catch (Throwable) {
            return true;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSkippedDecision(): array
    {
        return [
            'decision' => 'allow',
            'reason_code' => 'excluded_path',
            'risk_score' => 0,
            'rule_version' => (string) config('client_env.decision.rule_version', 'v1'),
        ];
    }

    /**
     * @param array<string, mixed> $decision
     */
    private function buildDeniedResponse(Request $request, array $decision)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Access denied by client environment policy.',
                'reason_code' => (string) ($decision['reason_code'] ?? 'client_env_denied'),
                'redirect_url' => route('client-env.access-reminder'),
            ], 403);
        }

        return redirect()->route('client-env.access-reminder');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEntry(Request $request): array
    {
        $serverDetect = $this->detectorService->detect($request);
        $userKey = $this->resolveUserKey($request->ip());
        $userAgent = (string) $request->userAgent();
        $uniqueKey = $this->buildUniqueKey($userKey, $userAgent, $serverDetect);

        return [
            'timestamp' => now()->toIso8601String(),
            'unique_key' => $uniqueKey,
            'request_id' => (string) ($request->headers->get('X-Request-Id') ?: Str::uuid()),
            'user_key' => $userKey,
            'ip' => $request->ip(),
            'user_agent' => $userAgent,
            'server_detect' => $serverDetect,
            'client_reported' => $this->resolveClientReported($request),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveClientReported(Request $request): ?array
    {
        $inputKey = (string) config('client_env.middleware.input_key', 'client');
        $clientFromInput = $request->input($inputKey);
        if (is_array($clientFromInput)) {
            return $clientFromInput;
        }

        $headerName = (string) config('client_env.middleware.header_name', 'X-Client-Env');
        $headerValue = trim((string) $request->headers->get($headerName, ''));
        if ($headerValue === '') {
            return null;
        }

        $decoded = json_decode($headerValue, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return null;
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
