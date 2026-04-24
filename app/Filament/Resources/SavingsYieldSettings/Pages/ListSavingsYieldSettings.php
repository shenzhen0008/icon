<?php

namespace App\Filament\Resources\SavingsYieldSettings\Pages;

use App\Filament\Resources\SavingsYieldSettings\SavingsYieldSettingResource;
use App\Modules\Savings\Models\SavingsYieldSetting;
use Filament\Resources\Pages\ListRecords;

class ListSavingsYieldSettings extends ListRecords
{
    protected static string $resource = SavingsYieldSettingResource::class;

    public function mount(): void
    {
        SavingsYieldSetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'daily_rate' => '0.0000',
            'is_active' => false,
        ]);

        parent::mount();
    }
}
