<?php

namespace App\Filament\Resources\ReferralCommissionSettings;

use App\Filament\Resources\ReferralCommissionSettings\Pages\EditReferralCommissionSetting;
use App\Filament\Resources\ReferralCommissionSettings\Pages\ListReferralCommissionSettings;
use App\Filament\Resources\ReferralCommissionSettings\Schemas\ReferralCommissionSettingForm;
use App\Modules\Referral\Models\ReferralCommissionSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ReferralCommissionSettingResource extends Resource
{
    protected static ?string $model = ReferralCommissionSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = '推荐提成比例';

    protected static ?string $modelLabel = '推荐提成比例';

    protected static ?string $pluralModelLabel = '推荐提成比例';

    protected static ?int $navigationSort = 14;

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => 1]);
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralCommissionSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level_1_rate')->label('一级提成比例'),
                TextColumn::make('level_2_rate')->label('二级提成比例'),
                IconColumn::make('is_active')->label('启用')->boolean(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralCommissionSettings::route('/'),
            'edit' => EditReferralCommissionSetting::route('/{record}/edit'),
        ];
    }
}
