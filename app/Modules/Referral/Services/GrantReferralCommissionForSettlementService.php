<?php

namespace App\Modules\Referral\Services;

use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Referral\Models\ReferralCommissionRecord;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Support\Facades\DB;

class GrantReferralCommissionForSettlementService
{
    public function __construct(private readonly GetReferralCommissionSettingService $getSettingService)
    {
    }

    /**
     * @return array{granted:int, skipped:int, failed:int}
     */
    public function handle(DailySettlement $settlement): array
    {
        if (! (bool) config('referral.enabled', true)) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        $setting = $this->getSettingService->handle();
        if ($setting === null) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        if ((float) $settlement->profit <= 0) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        $goLiveDate = (string) config('referral.go_live_date');
        if ($settlement->settlement_date->toDateString() < $goLiveDate) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        $settlement->loadMissing('user');
        $levelOneReferrerId = $settlement->user?->referrer_id;
        if ($levelOneReferrerId === null) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        $levelOneReferrer = User::query()->find($levelOneReferrerId);
        if ($levelOneReferrer === null) {
            return ['granted' => 0, 'skipped' => 1, 'failed' => 0];
        }

        $levels = [
            1 => [
                'referrer_id' => $levelOneReferrer->id,
                'rate' => (string) $setting->level_1_rate,
            ],
        ];

        if ($levelOneReferrer->referrer_id !== null) {
            $levels[2] = [
                'referrer_id' => $levelOneReferrer->referrer_id,
                'rate' => (string) $setting->level_2_rate,
            ];
        }

        $result = ['granted' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($levels as $level => $payload) {
            $amount = round((float) $settlement->profit * (float) $payload['rate'], 2);

            if ($amount <= 0) {
                $result['skipped']++;
                continue;
            }

            try {
                $granted = $this->grantLevel(
                    settlement: $settlement,
                    level: (int) $level,
                    referrerId: (int) $payload['referrer_id'],
                    rate: (string) $payload['rate'],
                    amount: $amount,
                );

                $granted ? $result['granted']++ : $result['skipped']++;
            } catch (\Throwable) {
                $result['failed']++;
            }
        }

        return $result;
    }

    private function grantLevel(DailySettlement $settlement, int $level, int $referrerId, string $rate, float $amount): bool
    {
        return DB::transaction(function () use ($settlement, $level, $referrerId, $rate, $amount): bool {
            $record = ReferralCommissionRecord::query()->firstOrCreate([
                'settlement_id' => $settlement->id,
                'level' => $level,
            ], [
                'referrer_id' => $referrerId,
                'referred_user_id' => $settlement->user_id,
                'base_profit' => $settlement->profit,
                'commission_rate' => $rate,
                'commission_amount' => $amount,
                'status' => ReferralCommissionRecord::STATUS_FAILED,
            ]);

            if ($record->status === ReferralCommissionRecord::STATUS_SUCCESS) {
                return false;
            }

            $bizRefId = 'settlement:'.$settlement->id.':level:'.$level;

            $existingLedger = BalanceLedger::query()
                ->where('user_id', $referrerId)
                ->where('type', 'referral_commission_credit')
                ->where('biz_ref_type', 'referral_commission')
                ->where('biz_ref_id', $bizRefId)
                ->first();

            if ($existingLedger !== null) {
                $record->update([
                    'status' => ReferralCommissionRecord::STATUS_SUCCESS,
                    'failed_reason' => null,
                    'granted_at' => $record->granted_at ?? now(),
                ]);

                return false;
            }

            $referrer = User::query()->lockForUpdate()->findOrFail($referrerId);
            $beforeBalance = (float) $referrer->balance;
            $afterBalance = $beforeBalance + $amount;

            BalanceLedger::query()->create([
                'user_id' => $referrerId,
                'type' => 'referral_commission_credit',
                'amount' => $amount,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'biz_ref_type' => 'referral_commission',
                'biz_ref_id' => $bizRefId,
                'settlement_date' => $settlement->settlement_date->toDateString(),
                'occurred_at' => now(),
            ]);

            $referrer->balance = $afterBalance;
            $referrer->save();

            $record->update([
                'referrer_id' => $referrerId,
                'referred_user_id' => $settlement->user_id,
                'base_profit' => $settlement->profit,
                'commission_rate' => $rate,
                'commission_amount' => $amount,
                'status' => ReferralCommissionRecord::STATUS_SUCCESS,
                'failed_reason' => null,
                'granted_at' => now(),
            ]);

            return true;
        });
    }
}
