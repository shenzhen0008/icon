<?php

namespace App\Filament\Resources\ExchangeMetrics;

use App\Filament\Resources\ExchangeMetrics\Pages\CreateExchangeMetric;
use App\Filament\Resources\ExchangeMetrics\Pages\EditExchangeMetric;
use App\Filament\Resources\ExchangeMetrics\Pages\ListExchangeMetrics;
use App\Filament\Resources\ExchangeMetrics\Schemas\ExchangeMetricForm;
use App\Filament\Resources\ExchangeMetrics\Tables\ExchangeMetricsTable;
use App\Modules\Exchange\Models\ExchangeMetric;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExchangeMetricResource extends Resource
{
    protected static ?string $model = ExchangeMetric::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = '操盘平台';

    protected static ?string $modelLabel = '操盘平台';

    protected static ?string $pluralModelLabel = '操盘平台';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return ExchangeMetricForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExchangeMetricsTable::configure($table);
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
            'index' => ListExchangeMetrics::route('/'),
            'create' => CreateExchangeMetric::route('/create'),
            'edit' => EditExchangeMetric::route('/{record}/edit'),
        ];
    }
}
