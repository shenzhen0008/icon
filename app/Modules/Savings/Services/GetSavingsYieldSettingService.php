<?php

namespace App\Modules\Savings\Services;

use App\Modules\Savings\Models\SavingsYieldSetting;

class GetSavingsYieldSettingService
{
    public function handle(): ?SavingsYieldSetting
    {
        return SavingsYieldSetting::query()
            ->whereKey(1)
            ->where('is_active', true)
            ->first();
    }
}
