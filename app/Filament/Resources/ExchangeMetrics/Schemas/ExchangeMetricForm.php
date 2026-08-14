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
                        TextInput::make('display_btc_liquidity')
                            ->label('BTC Liquidity')
                            ->required(),
                        TextInput::make('display_eth_liquidity')
                            ->label('ETH Liquidity')
                            ->required(),
                    ]),
                Section::make('Liquidity 跳动设置')
                    ->description('当前交易平台的 BTC/ETH Liquidity 共用这组跳动范围；24h Volume 会按首页展示数值里的 BTC/ETH 倍率自动计算。')
                    ->columns(3)
                    ->schema([
                        TextInput::make('liquidity_step_seconds')
                            ->label('跳动秒数')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                        TextInput::make('liquidity_min_delta')
                            ->label('跳动范围最小值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                        TextInput::make('liquidity_max_delta')
                            ->label('跳动范围最大值')
                            ->required()
                            ->numeric()
                            ->step('0.01'),
                    ]),
            ]);
    }
}
