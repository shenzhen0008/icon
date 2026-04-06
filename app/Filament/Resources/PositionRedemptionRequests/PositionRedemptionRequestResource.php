<?php

namespace App\Filament\Resources\PositionRedemptionRequests;

use App\Filament\Resources\PositionRedemptionRequests\Pages\ListPositionRedemptionRequests;
use App\Filament\Resources\PositionRedemptionRequests\Tables\PositionRedemptionRequestsTable;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PositionRedemptionRequestResource extends Resource
{
    protected static ?string $model = PositionRedemptionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $navigationLabel = '赎回申请';

    protected static ?string $modelLabel = '赎回申请';

    protected static ?string $pluralModelLabel = '赎回申请';

    protected static ?int $navigationSort = 11;

    public static function table(Table $table): Table
    {
        return PositionRedemptionRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPositionRedemptionRequests::route('/'),
        ];
    }
}
