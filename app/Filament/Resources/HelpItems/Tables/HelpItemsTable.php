<?php

namespace App\Filament\Resources\HelpItems\Tables;

use App\Modules\Help\Models\HelpItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HelpItemsTable
{
    public static function configure(Table $table): Table
    {
        $fallbackLocale = (string) config('i18n.fallback_locale', 'zh-CN');

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('translations'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('default_question')
                    ->label('默认语言问题')
                    ->getStateUsing(function (HelpItem $record) use ($fallbackLocale): string {
                        $translation = $record->translations->firstWhere('locale', $fallbackLocale)
                            ?? $record->translations->first();

                        return (string) ($translation?->question ?? '--');
                    }),
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
