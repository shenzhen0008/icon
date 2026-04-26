<?php

namespace App\Modules\Settlement\Console\Commands;

use App\Modules\Settlement\Services\RunDailyIncomeSettlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ProcessDailySettlementCommand extends Command
{
    protected $signature = 'settlement:daily {--date= : Settlement date in Y-m-d format}';

    protected $description = 'Process daily settlement for all products.';

    public function handle(RunDailyIncomeSettlementService $runDailyIncomeSettlementService): int
    {
        $dateOption = $this->option('date');
        $date = is_string($dateOption) && $dateOption !== ''
            ? $this->normalizeDate($dateOption)
            : now(config('settlement.timezone', 'Asia/Shanghai'))->toDateString();

        $result = $runDailyIncomeSettlementService->handle($date);

        $this->info(sprintf('daily settlement finished for %s', $date));
        if (! $result['lock_acquired']) {
            $this->warn((string) $result['message']);

            return self::SUCCESS;
        }

        $principalReturnStats = $result['principal_return'];
        $savingsStats = $result['savings_yield'];
        $referralStats = $result['referral_commission'];

        $this->info(sprintf(
            'principal return scanned=%d returned=%d skipped=%d failed=%d',
            $principalReturnStats['scanned'],
            $principalReturnStats['returned'],
            $principalReturnStats['skipped'],
            $principalReturnStats['failed'],
        ));
        $this->info(sprintf(
            'savings yield scanned=%d granted=%d skipped=%d failed=%d',
            $savingsStats['scanned'],
            $savingsStats['granted'],
            $savingsStats['skipped'],
            $savingsStats['failed'],
        ));
        $this->info(sprintf(
            'referral commission scanned=%d granted=%d skipped=%d failed=%d',
            $referralStats['scanned'],
            $referralStats['granted'],
            $referralStats['skipped'],
            $referralStats['failed'],
        ));

        return self::SUCCESS;
    }

    private function normalizeDate(string $date): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'date' => 'The --date option must use Y-m-d format.',
            ]);
        }
    }
}
