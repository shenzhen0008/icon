<?php

namespace App\Filament\Resources\Users\Tables;

use App\Modules\PopupPush\Services\PopupCampaignService;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('username')
                    ->label('用户名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('remark')
                    ->label('备注')
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->remark),
                TextColumn::make('balance')
                    ->label('余额(USDT)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('创建时间')
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
                BulkAction::make('push_popup')
                    ->label('发送弹窗')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('向已选用户发送弹窗')
                    ->form([
                        Textarea::make('content')
                            ->label('内容')
                            ->required()
                            ->rows(5)
                            ->maxLength(3000),
                        Toggle::make('requires_ack')
                            ->label('仅允许确认（禁用关闭）')
                            ->default(false),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        try {
                            $campaign = app(PopupCampaignService::class)->createAndSend(
                                $records->pluck('id')->map(static fn ($id): int => (int) $id)->all(),
                                $data,
                                (int) auth()->id(),
                            );

                            Notification::make()
                                ->title('弹窗发送成功')
                                ->body(sprintf('活动 #%d 已发送给 %d 位用户。', $campaign->id, $records->count()))
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);
                            Notification::make()
                                ->title('发送失败')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteBulkAction::make(),
            ]);
    }
}
