<?php

namespace App\Modules\Referral\Services;

use App\Modules\Settlement\Models\DailySettlement;

class ProcessReferralCommissionBatchService
{
    public function __construct(
        private readonly GetReferralCommissionSettingService $getSettingService,
        private readonly GrantReferralCommissionForSettlementService $grantService,
    ) {
    }

    /**
     * @return array{scanned:int, granted:int, skipped:int, failed:int, message:string|null}
     */
    public function handle(): array
    {
        $stats = [
            'scanned' => 0,
            'granted' => 0,
            'skipped' => 0,
            'failed' => 0,
            'message' => null,
        ];

        if (! (bool) config('referral.enabled', true)) {
            $stats['message'] = 'Referral commission is disabled.';

            return $stats;
        }

        if ($this->getSettingService->handle() === null) {
            $stats['message'] = 'Active referral commission setting is missing.';

            return $stats;
        }

        DailySettlement::query()
            ->where('profit', '>', 0)
            ->whereDate('settlement_date', '>=', (string) config('referral.go_live_date'))
            ->orderBy('id')
            ->chunkById((int) config('referral.batch_chunk_size'), function ($settlements) use (&$stats): void {
                foreach ($settlements as $settlement) {
                    $stats['scanned']++;

                    $result = $this->grantService->handle($settlement);
                    $stats['granted'] += $result['granted'];
                    $stats['skipped'] += $result['skipped'];
                    $stats['failed'] += $result['failed'];
                }
            });

        return $stats;
    }
}
