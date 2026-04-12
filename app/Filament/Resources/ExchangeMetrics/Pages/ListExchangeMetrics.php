<?php

namespace App\Filament\Resources\ExchangeMetrics\Pages;

use App\Filament\Resources\ExchangeMetrics\ExchangeMetricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExchangeMetrics extends ListRecords
{
    protected static string $resource = ExchangeMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
