<?php

namespace App\Filament\Resources\ReferralCommissionSettings\Pages;

use App\Filament\Resources\ReferralCommissionSettings\ReferralCommissionSettingResource;
use App\Modules\Referral\Models\ReferralCommissionSetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralCommissionSetting extends EditRecord
{
    protected static string $resource = ReferralCommissionSettingResource::class;

    public function mount(int|string $record): void
    {
        ReferralCommissionSetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'level_1_rate' => '0.0500',
            'level_2_rate' => '0.0200',
            'is_active' => true,
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
