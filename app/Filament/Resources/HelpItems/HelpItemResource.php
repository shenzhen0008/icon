<?php

namespace App\Filament\Resources\HelpItems;

use App\Filament\Resources\HelpItems\Pages\CreateHelpItem;
use App\Filament\Resources\HelpItems\Pages\EditHelpItem;
use App\Filament\Resources\HelpItems\Pages\ListHelpItems;
use App\Filament\Resources\HelpItems\Schemas\HelpItemForm;
use App\Filament\Resources\HelpItems\Tables\HelpItemsTable;
use App\Modules\Help\Models\HelpItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HelpItemResource extends Resource
{
    protected static ?string $model = HelpItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = '帮助FAQ';

    protected static ?string $modelLabel = 'FAQ项';

    protected static ?string $pluralModelLabel = '帮助FAQ';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return HelpItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpItems::route('/'),
            'create' => CreateHelpItem::route('/create'),
            'edit' => EditHelpItem::route('/{record}/edit'),
        ];
    }
}
