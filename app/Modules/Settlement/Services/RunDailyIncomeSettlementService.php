<?php

namespace App\Modules\Settlement\Services;

use App\Modules\Referral\Services\ProcessReferralCommissionBatchService;
use App\Modules\Savings\Services\ProcessSavingsYieldBatchService;
use Illuminate\Support\Facades\Cache;

class RunDailyIncomeSettlementService
{
    public function __construct(
        private readonly DailySettlementService $dailySettlementService,
        private readonly ProcessMaturedPositionPrincipalReturnService $processMaturedPositionPrincipalReturnService,
        private readonly ProcessSavingsYieldBatchService $processSavingsYieldBatchService,
        private readonly ProcessReferralCommissionBatchService $processReferralCommissionBatchService,
    ) {
    }

    /**
     * @return array{
     *     date:string,
     *     lock_acquired:bool,
     *     product_settlement:array{status:string},
     *     principal_return:array{scanned:int,returned:int,skipped:int,failed:int,message:string|null},
     *     savings_yield:array{scanned:int,granted:int,skipped:int,failed:int,message:string|null},
     *     referral_commission:array{scanned:int,granted:int,skipped:int,failed:int,message:string|null},
     *     message:string|null
     * }
     */
    public function handle(string $date): array
    {
        $lock = Cache::lock('daily-income-settlement:'.$date, 600);

        if (! $lock->get()) {
            return [
                'date' => $date,
                'lock_acquired' => false,
                'product_settlement' => ['status' => 'skipped'],
                'principal_return' => [
                    'scanned' => 0,
                    'returned' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                    'message' => 'Skipped because another settlement process is running.',
                ],
                'savings_yield' => [
                    'scanned' => 0,
                    'granted' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                    'message' => 'Skipped because another settlement process is running.',
                ],
                'referral_commission' => [
                    'scanned' => 0,
                    'granted' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                    'message' => 'Skipped because another settlement process is running.',
                ],
                'message' => 'Skipped because another settlement process is running.',
            ];
        }

        try {
            $this->dailySettlementService->settleAllProductsByDate($date);
            $principalReturnStats = $this->processMaturedPositionPrincipalReturnService->handle($date);
            $savingsStats = $this->processSavingsYieldBatchService->handle($date);
            $referralStats = $this->processReferralCommissionBatchService->handle();

            return [
                'date' => $date,
                'lock_acquired' => true,
                'product_settlement' => ['status' => 'completed'],
                'principal_return' => $principalReturnStats,
                'savings_yield' => $savingsStats,
                'referral_commission' => $referralStats,
                'message' => null,
            ];
        } finally {
            optional($lock)->release();
        }
    }
}
