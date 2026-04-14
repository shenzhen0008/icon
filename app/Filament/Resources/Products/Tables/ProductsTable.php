<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('产品名称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('产品代码')
                    ->searchable(),
                TextColumn::make('trade_mode')
                    ->label('交易模式')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'reserve' ? '预订商品' : '正式商品'),
                TextColumn::make('limit_min_usdt')
                    ->label('最低限额')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('limit_max_usdt')
                    ->label('最高限额')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('rate_min_percent')
                    ->label('最低收益率(%)')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('rate_max_percent')
                    ->label('最高收益率(%)')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('cycle_days')
                    ->label('周期(天)'),
                TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('上架')
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
