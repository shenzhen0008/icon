<?php

namespace App\Filament\Resources\SavingsYieldSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SavingsYieldSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('储蓄收益率设置')
                    ->columns(2)
                    ->schema([
                        TextInput::make('daily_rate')
                            ->label('日收益率')
                            ->helperText('例如 0.0030 表示 0.30%')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->rule('lt:1'),
                        Toggle::make('is_active')
                            ->label('启用')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
