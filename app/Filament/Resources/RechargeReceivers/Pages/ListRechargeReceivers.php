<?php

namespace App\Filament\Resources\RechargeReceivers\Pages;

use App\Filament\Resources\RechargeReceivers\RechargeReceiverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRechargeReceivers extends ListRecords
{
    protected static string $resource = RechargeReceiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
