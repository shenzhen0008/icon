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
                Section::make('盘口数据')
                    ->columns(2)
                    ->schema([
                        TextInput::make('btc_value')
                            ->label('BTC 价格')
                            ->numeric()
                            ->step('0.00000001')
                            ->required(),
                        TextInput::make('btc_liquidity')
                            ->label('BTC 流动性')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('eth_value')
                            ->label('ETH 价格')
                            ->numeric()
                            ->step('0.00000001')
                            ->required(),
                        TextInput::make('eth_liquidity')
                            ->label('ETH 流动性')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('total_value')
                            ->label('总价值')
                            ->numeric()
                            ->step('0.00000001')
                            ->default(0)
                            ->required(),
                        TextInput::make('profit_value')
                            ->label('利润值')
                            ->numeric()
                            ->step('0.01')
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }
}
