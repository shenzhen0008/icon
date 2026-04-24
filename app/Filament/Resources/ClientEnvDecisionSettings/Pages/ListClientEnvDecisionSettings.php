<?php

namespace App\Filament\Resources\ClientEnvDecisionSettings\Pages;

use App\Filament\Resources\ClientEnvDecisionSettings\ClientEnvDecisionSettingResource;
use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use Filament\Resources\Pages\ListRecords;

class ListClientEnvDecisionSettings extends ListRecords
{
    protected static string $resource = ClientEnvDecisionSettingResource::class;

    public function mount(): void
    {
        ClientEnvDecisionSetting::query()->firstOrCreate(
            ['id' => 1],
            ['is_enabled' => true],
        );

        parent::mount();
    }
}
