<?php

namespace App\Filament\Resources\ReferralCommissionSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReferralCommissionSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('推荐提成比例')
                    ->columns(2)
                    ->schema([
                        TextInput::make('level_1_rate')
                            ->label('一级提成比例')
                            ->helperText('例如 0.05 表示 5%')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->rule('lt:1'),
                        TextInput::make('level_2_rate')
                            ->label('二级提成比例')
                            ->helperText('例如 0.02 表示 2%')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->lte('level_1_rate'),
                        Toggle::make('is_active')
                            ->label('启用')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
