<?php

namespace App\Modules\ClientEnv\Services;

class ClientEnvDecisionService
{
    /**
     * @param array<string, mixed> $probe
     * @return array{decision: string, reason_code: string, risk_score: int, rule_version: string}
     */
    public function decide(array $probe): array
    {
        $userAgent = trim((string) ($probe['user_agent'] ?? ''));
        $normalizedUa = strtolower($userAgent);
        $keywords = array_filter(
            array_map(
                static fn (mixed $keyword): string => strtolower(trim((string) $keyword)),
                (array) config('client_env.decision.wallet_keywords', [])
            ),
            static fn (string $keyword): bool => $keyword !== ''
        );

        foreach ($keywords as $keyword) {
            if (str_contains($normalizedUa, $keyword)) {
                return $this->allow('layer1_allow_wallet_keyword');
            }
        }

        return $this->deny('layer1_deny_wallet_keyword_not_matched', 100);
    }

    /**
     * @return array{decision: string, reason_code: string, risk_score: int, rule_version: string}
     */
    private function deny(string $reasonCode, int $riskScore): array
    {
        return [
            'decision' => 'deny',
            'reason_code' => $reasonCode,
            'risk_score' => min(max($riskScore, 0), 100),
            'rule_version' => (string) config('client_env.decision.rule_version', 'v1'),
        ];
    }

    /**
     * @return array{decision: string, reason_code: string, risk_score: int, rule_version: string}
     */
    private function allow(string $reasonCode): array
    {
        return [
            'decision' => 'allow',
            'reason_code' => $reasonCode,
            'risk_score' => 0,
            'rule_version' => (string) config('client_env.decision.rule_version', 'v1'),
        ];
    }
}
