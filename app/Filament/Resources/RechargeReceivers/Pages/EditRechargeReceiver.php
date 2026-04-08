<?php

namespace App\Filament\Resources\RechargeReceivers\Pages;

use App\Filament\Resources\RechargeReceivers\RechargeReceiverResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRechargeReceiver extends EditRecord
{
    protected static string $resource = RechargeReceiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(false),
        ];
    }
}
