<?php

namespace App\Filament\Resources\HelpItems\Pages;

use App\Filament\Resources\HelpItems\HelpItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpItems extends ListRecords
{
    protected static string $resource = HelpItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
