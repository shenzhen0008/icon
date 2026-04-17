<?php

namespace App\Filament\Resources\HelpItems\Pages;

use App\Filament\Resources\HelpItems\HelpItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHelpItem extends EditRecord
{
    protected static string $resource = HelpItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
