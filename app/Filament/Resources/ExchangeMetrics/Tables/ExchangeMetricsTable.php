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
                TextColumn::make('display_btc_volume')
                    ->label('BTC 24h Volume'),
                TextColumn::make('display_btc_liquidity')
                    ->label('BTC Liquidity'),
                TextColumn::make('display_eth_volume')
                    ->label('ETH 24h Volume'),
                TextColumn::make('display_eth_liquidity')
                    ->label('ETH Liquidity'),
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
