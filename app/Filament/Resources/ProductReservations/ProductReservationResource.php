<?php

namespace App\Filament\Resources\ProductReservations;

use App\Filament\Resources\ProductReservations\Pages\ListProductReservations;
use App\Filament\Resources\ProductReservations\Tables\ProductReservationsTable;
use App\Modules\Reservation\Models\ProductReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductReservationResource extends Resource
{
    protected static ?string $model = ProductReservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = '预订订单管理';

    protected static ?string $modelLabel = '预订订单';

    protected static ?string $pluralModelLabel = '预订订单管理';

    protected static ?int $navigationSort = 12;

    public static function table(Table $table): Table
    {
        return ProductReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductReservations::route('/'),
        ];
    }
}
