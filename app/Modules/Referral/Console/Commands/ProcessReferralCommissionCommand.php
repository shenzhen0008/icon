<?php

namespace App\Modules\Referral\Console\Commands;

use App\Modules\Referral\Services\ProcessReferralCommissionBatchService;
use Illuminate\Console\Command;

class ProcessReferralCommissionCommand extends Command
{
    protected $signature = 'referral:commission-process';

    protected $description = 'Process MVP two-level referral commissions for daily settlements.';

    public function handle(ProcessReferralCommissionBatchService $service): int
    {
        $stats = $service->handle();

        if ($stats['message'] !== null) {
            $this->info($stats['message']);
        }

        $this->info(sprintf(
            'scanned=%d granted=%d skipped=%d failed=%d',
            $stats['scanned'],
            $stats['granted'],
            $stats['skipped'],
            $stats['failed'],
        ));

        return self::SUCCESS;
    }
}
