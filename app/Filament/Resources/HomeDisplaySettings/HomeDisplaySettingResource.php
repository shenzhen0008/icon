<?php

namespace App\Filament\Resources\HomeDisplaySettings;

use App\Filament\Resources\HomeDisplaySettings\Pages\EditHomeDisplaySetting;
use App\Filament\Resources\HomeDisplaySettings\Pages\ListHomeDisplaySettings;
use App\Filament\Resources\HomeDisplaySettings\Schemas\HomeDisplaySettingForm;
use App\Modules\Home\Models\HomeDisplaySetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeDisplaySettingResource extends Resource
{
    protected static ?string $model = HomeDisplaySetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $navigationLabel = '首页展示数值';

    protected static ?string $modelLabel = '首页展示数值';

    protected static ?string $pluralModelLabel = '首页展示数值';

    protected static ?int $navigationSort = 39;

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => 1]);
    }

    public static function form(Schema $schema): Schema
    {
        return HomeDisplaySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('summary_people_count')->label('Number of people'),
                TextColumn::make('summary_total_profit')->label('总盘获利值'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomeDisplaySettings::route('/'),
            'edit' => EditHomeDisplaySetting::route('/{record}/edit'),
        ];
    }
}
