<?php

namespace App\Filament\Resources\HomeDisplaySettings\Pages;

use App\Filament\Resources\HomeDisplaySettings\HomeDisplaySettingResource;
use App\Modules\Home\Models\HomeDisplaySetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeDisplaySetting extends EditRecord
{
    protected static string $resource = HomeDisplaySettingResource::class;

    public function mount(int|string $record): void
    {
        HomeDisplaySetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'summary_people_count' => '0',
            'summary_total_profit' => '0.00 USDT',
        ]);

        parent::mount(1);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
