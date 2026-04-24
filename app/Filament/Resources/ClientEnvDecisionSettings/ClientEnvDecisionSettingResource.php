<?php

namespace App\Filament\Resources\ClientEnvDecisionSettings;

use App\Filament\Resources\ClientEnvDecisionSettings\Pages\EditClientEnvDecisionSetting;
use App\Filament\Resources\ClientEnvDecisionSettings\Pages\ListClientEnvDecisionSettings;
use App\Filament\Resources\ClientEnvDecisionSettings\Schemas\ClientEnvDecisionSettingForm;
use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ClientEnvDecisionSettingResource extends Resource
{
    protected static ?string $model = ClientEnvDecisionSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = '环境检测风控开关';

    protected static ?string $modelLabel = '环境检测风控开关';

    protected static ?string $pluralModelLabel = '环境检测风控开关';

    protected static ?int $navigationSort = 16;

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => 1]);
    }

    public static function form(Schema $schema): Schema
    {
        return ClientEnvDecisionSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')->label('第二层判定启用')->boolean(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientEnvDecisionSettings::route('/'),
            'edit' => EditClientEnvDecisionSetting::route('/{record}/edit'),
        ];
    }
}

