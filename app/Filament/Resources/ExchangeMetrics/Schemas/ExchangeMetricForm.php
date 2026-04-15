<?php

namespace App\Filament\Resources\ExchangeMetrics\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExchangeMetricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基础信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('exchange_name')
                            ->label('交易所名称')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('exchange_code')
                            ->label('交易所代码')
                            ->required()
                            ->alphaDash()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('启用')
                            ->default(true),
                    ]),
                Section::make('展示数据')
                    ->columns(2)
                    ->schema([
                        TextInput::make('display_btc_volume')
                            ->label('BTC 24h Volume')
                            ->required(),
                        TextInput::make('display_btc_liquidity')
                            ->label('BTC Liquidity')
                            ->required(),
                        TextInput::make('display_eth_volume')
                            ->label('ETH 24h Volume')
                            ->required(),
                        TextInput::make('display_eth_liquidity')
                            ->label('ETH Liquidity')
                            ->required(),
                    ]),
            ]);
    }
}
