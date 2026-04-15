<?php

namespace App\Filament\Resources\HomeDisplaySettings\Pages;

use App\Filament\Resources\HomeDisplaySettings\HomeDisplaySettingResource;
use App\Modules\Home\Models\HomeDisplaySetting;
use Filament\Resources\Pages\ListRecords;

class ListHomeDisplaySettings extends ListRecords
{
    protected static string $resource = HomeDisplaySettingResource::class;

    public function mount(): void
    {
        HomeDisplaySetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'summary_people_count' => '0',
            'summary_total_profit' => '0.00 USDT',
        ]);

        parent::mount();
    }
}
