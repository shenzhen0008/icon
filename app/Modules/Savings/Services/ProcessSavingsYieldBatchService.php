<?php

namespace App\Modules\Savings\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use Illuminate\Support\Facades\DB;

class ProcessSavingsYieldBatchService
{
    public function __construct(private readonly GetSavingsYieldSettingService $getSettingService)
    {
    }

    /**
     * @return array{scanned:int, granted:int, skipped:int, failed:int, message:string|null}
     */
    public function handle(string $date): array
    {
        $stats = [
            'scanned' => 0,
            'granted' => 0,
            'skipped' => 0,
            'failed' => 0,
            'message' => null,
        ];

        $setting = $this->getSettingService->handle();
        if ($setting === null) {
            $stats['message'] = 'Savings yield setting is missing or inactive.';

            return $stats;
        }

        $rate = (float) $setting->daily_rate;
        if ($rate <= 0) {
            $stats['message'] = 'Savings yield rate is zero.';

            return $stats;
        }

        User::query()
            ->where('balance', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($users) use (&$stats, $date, $rate): void {
                foreach ($users as $user) {
                    $stats['scanned']++;

                    try {
                        $granted = $this->settleOneUser((int) $user->id, $date, $rate);
                        $granted ? $stats['granted']++ : $stats['skipped']++;
                    } catch (\Throwable $exception) {
                        report($exception);
                        $stats['failed']++;
                    }
                }
            });

        return $stats;
    }

    private function settleOneUser(int $userId, string $date, float $rate): bool
    {
        return DB::transaction(function () use ($userId, $date, $rate): bool {
            $bizRefId = sprintf('%s:%d', $date, $userId);

            $existingLedger = BalanceLedger::query()
                ->where('user_id', $userId)
                ->where('type', 'savings_interest_credit')
                ->where('biz_ref_type', 'savings_interest')
                ->where('biz_ref_id', $bizRefId)
                ->exists();

            if ($existingLedger) {
                return false;
            }

            $user = User::query()
                ->lockForUpdate()
                ->find($userId);

            if ($user === null) {
                return false;
            }

            $beforeBalance = (float) $user->balance;
            if ($beforeBalance <= 0) {
                return false;
            }

            $profit = round($beforeBalance * $rate, 2);
            if ($profit <= 0) {
                return false;
            }

            $afterBalance = $beforeBalance + $profit;

            $user->balance = $afterBalance;
            $user->save();

            BalanceLedger::query()->create([
                'user_id' => $userId,
                'type' => 'savings_interest_credit',
                'amount' => $profit,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'savings_interest',
                'biz_ref_id' => $bizRefId,
                'settlement_date' => $date,
                'occurred_at' => now(),
            ]);

            return true;
        });
    }
}
