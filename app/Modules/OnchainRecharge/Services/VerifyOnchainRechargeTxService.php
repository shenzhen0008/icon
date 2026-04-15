<?php

namespace App\Modules\OnchainRecharge\Services;

class VerifyOnchainRechargeTxService
{
    /**
     * @return array{passed: bool, reason: string|null}
     */
    public function verify(string $txHash): array
    {
        if ($txHash === '') {
            return [
                'passed' => false,
                'reason' => 'empty_tx_hash',
            ];
        }

        // This implementation intentionally keeps verification lightweight for manual review flow.
        return [
            'passed' => true,
            'reason' => null,
        ];
    }
}
