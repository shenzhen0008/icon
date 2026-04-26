<?php

namespace App\Modules\Settlement\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessMaturedPositionPrincipalReturnService
{
    /**
     * @return array{scanned:int,returned:int,skipped:int,failed:int,message:string|null}
     */
    public function handle(string $date): array
    {
        $stats = [
            'scanned' => 0,
            'returned' => 0,
            'skipped' => 0,
            'failed' => 0,
            'message' => null,
        ];

        Position::query()
            ->where('status', 'open')
            ->orderBy('id')
            ->chunkById(200, function ($positions) use (&$stats, $date): void {
                foreach ($positions as $position) {
                    $stats['scanned']++;

                    try {
                        $returned = $this->settleOnePosition((int) $position->id, $date);
                        $returned ? $stats['returned']++ : $stats['skipped']++;
                    } catch (\Throwable $exception) {
                        report($exception);
                        $stats['failed']++;
                    }
                }
            });

        return $stats;
    }

    private function settleOnePosition(int $positionId, string $date): bool
    {
        return DB::transaction(function () use ($positionId, $date): bool {
            $position = Position::query()
                ->with('product:id,cycle_days')
                ->lockForUpdate()
                ->find($positionId);

            if ($position === null || $position->status !== 'open') {
                return false;
            }

            $cycleDays = $position->product?->cycle_days;
            if ($position->opened_at === null || ! is_numeric($cycleDays) || (int) $cycleDays <= 0) {
                return false;
            }

            $maturityAt = $position->opened_at->copy()->addDays((int) $cycleDays);
            $timezone = (string) config('settlement.timezone', 'Asia/Shanghai');
            $settlementCutoff = Carbon::createFromFormat('Y-m-d', $date, $timezone)->endOfDay();
            if ($maturityAt->greaterThan($settlementCutoff)) {
                return false;
            }

            $user = User::query()
                ->lockForUpdate()
                ->find($position->user_id);

            if ($user === null) {
                return false;
            }

            $principal = round((float) $position->principal, 2);
            if ($principal <= 0) {
                $position->status = 'closed';
                $position->closed_at = now();
                $position->save();

                return false;
            }

            $existingLedger = BalanceLedger::query()
                ->where('user_id', $position->user_id)
                ->where('type', 'principal_return_credit')
                ->where('biz_ref_type', 'position')
                ->where('biz_ref_id', (string) $position->id)
                ->exists();

            if ($existingLedger) {
                $position->status = 'closed';
                $position->closed_at = now();
                $position->save();

                return false;
            }

            $beforeBalance = (float) $user->balance;
            $afterBalance = $beforeBalance + $principal;

            $user->balance = $afterBalance;
            $user->save();

            BalanceLedger::query()->create([
                'user_id' => $position->user_id,
                'type' => 'principal_return_credit',
                'amount' => $principal,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'position',
                'biz_ref_id' => (string) $position->id,
                'occurred_at' => now(),
            ]);

            $position->status = 'closed';
            $position->closed_at = now();
            $position->save();

            return true;
        });
    }
}
