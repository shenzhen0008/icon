<?php

namespace App\Modules\ClientEnv\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientEnvDecisionAuditService
{
    /**
     * @param array<string, mixed> $probe
     * @param array<string, mixed> $decision
     */
    public function audit(Request $request, array $probe, array $decision): void
    {
        if (!(bool) config('client_env.decision.audit.enabled', true)) {
            return;
        }

        $decisionValue = (string) ($decision['decision'] ?? 'allow');
        if ($decisionValue !== 'deny' && !$this->shouldRecordAllow($request, $probe)) {
            return;
        }

        $ip = (string) ($probe['ip'] ?? '');
        $uniqueKey = (string) ($probe['unique_key'] ?? '');
        $routeKey = (string) ($request->route()?->uri() ?: $request->path());

        DB::table('client_env_decision_logs')->insert([
            'request_id' => (string) ($probe['request_id'] ?? ''),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip_hash' => hash('sha256', $ip),
            'fingerprint_hash' => hash('sha256', $uniqueKey),
            'decision' => $decisionValue === 'deny' ? 'deny' : 'allow',
            'reason_code' => (string) ($decision['reason_code'] ?? 'ok'),
            'risk_score' => (int) ($decision['risk_score'] ?? 0),
            'route_key' => mb_substr($routeKey, 0, 128),
            'rule_version' => (string) ($decision['rule_version'] ?? 'v1'),
            'created_at' => now(),
        ]);
    }

    /**
     * @param array<string, mixed> $probe
     */
    private function shouldRecordAllow(Request $request, array $probe): bool
    {
        $sampleRate = (int) config('client_env.decision.audit.allow_sample_rate', 10);
        $sampleRate = min(max($sampleRate, 0), 100);
        if ($sampleRate === 0) {
            return false;
        }

        if ($sampleRate < 100 && random_int(1, 100) > $sampleRate) {
            return false;
        }

        $ttl = (int) config('client_env.decision.audit.allow_dedupe_ttl_seconds', 86400);
        if ($ttl <= 0) {
            return true;
        }

        $uniqueKey = (string) ($probe['unique_key'] ?? '');
        $routeKey = (string) ($request->route()?->uri() ?: $request->path());
        $cacheKey = 'client_env:allow_audit:'.hash('sha256', $uniqueKey.'|'.$routeKey);
        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, 1, $ttl);

        return true;
    }
}

