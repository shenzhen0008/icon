<?php

namespace App\Filament\Resources\ExchangeMetrics\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExchangeMetricsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('exchange_code')
                    ->label('交易所代码')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exchange_name')
                    ->label('交易所名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profit_value')
                    ->label('利润值')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('btc_value')
                    ->label('BTC 价格')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('btc_liquidity')
                    ->label('BTC 流动性')
                    ->numeric(),
                TextColumn::make('eth_value')
                    ->label('ETH 价格')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('eth_liquidity')
                    ->label('ETH 流动性')
                    ->numeric(),
                TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('启用')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
