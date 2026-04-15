<?php

namespace App\Modules\Referral\Services;

use App\Modules\Referral\Models\ReferralCommissionSetting;

class GetReferralCommissionSettingService
{
    public function handle(): ?ReferralCommissionSetting
    {
        return ReferralCommissionSetting::query()
            ->whereKey(1)
            ->where('is_active', true)
            ->first();
    }
}
