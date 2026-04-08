<?php

namespace App\Filament\Resources\RechargeReceivers\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RechargeReceiversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('asset_code')
                    ->label('币种代码')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('asset_name')
                    ->label('币种名称')
                    ->searchable(),
                TextColumn::make('network')
                    ->label('网络'),
                TextColumn::make('address')
                    ->label('收款地址')
                    ->limit(32)
                    ->tooltip(fn ($record): string => (string) $record->address),
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
            ->defaultSort('sort')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
