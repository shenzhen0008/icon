<?php

namespace App\Modules\Settlement\Console\Commands;

use App\Modules\Settlement\Services\DailySettlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ProcessDailySettlementCommand extends Command
{
    protected $signature = 'settlement:daily {--date= : Settlement date in Y-m-d format}';

    protected $description = 'Process daily settlement for all products.';

    public function handle(DailySettlementService $service): int
    {
        $dateOption = $this->option('date');
        $date = is_string($dateOption) && $dateOption !== ''
            ? $this->normalizeDate($dateOption)
            : now(config('settlement.timezone', 'Asia/Shanghai'))->toDateString();

        $service->settleAllProductsByDate($date);

        $this->info(sprintf('daily settlement finished for %s', $date));

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

