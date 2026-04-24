<?php

namespace App\Filament\Resources\ClientEnvDecisionSettings\Pages;

use App\Filament\Resources\ClientEnvDecisionSettings\ClientEnvDecisionSettingResource;
use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientEnvDecisionSetting extends EditRecord
{
    protected static string $resource = ClientEnvDecisionSettingResource::class;

    public function mount(int|string $record): void
    {
        $setting = ClientEnvDecisionSetting::query()->find(1);
        if ($setting === null) {
            $setting = new ClientEnvDecisionSetting();
            $setting->id = 1;
            $setting->is_enabled = true;
            $setting->save();
        }

        parent::mount(1);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
