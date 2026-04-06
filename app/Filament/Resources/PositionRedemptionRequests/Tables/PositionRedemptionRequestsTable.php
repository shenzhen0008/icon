<?php

namespace App\Filament\Resources\PositionRedemptionRequests\Tables;

use App\Modules\Redemption\Models\PositionRedemptionRequest;
use App\Modules\Redemption\Services\ReviewPositionRedemptionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class PositionRedemptionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('用户')
                    ->searchable(),
                TextColumn::make('position_id')
                    ->label('持仓ID')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('产品'),
                TextColumn::make('position.principal')
                    ->label('赎回金额(USDT)')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge(),
                TextColumn::make('requested_at')
                    ->label('申请时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('审批时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('--'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('通过')
                    ->color('success')
                    ->visible(fn (PositionRedemptionRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('确认通过赎回申请')
                    ->form([
                        Textarea::make('review_remark')
                            ->label('审批备注')
                            ->maxLength(500),
                    ])
                    ->action(function (PositionRedemptionRequest $record, array $data): void {
                        try {
                            app(ReviewPositionRedemptionService::class)->approve(
                                (int) $record->id,
                                (int) auth()->id(),
                                $data['review_remark'] ?? null,
                            );

                            Notification::make()
                                ->title('赎回申请已通过')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);
                            Notification::make()
                                ->title('处理失败')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('拒绝')
                    ->color('danger')
                    ->visible(fn (PositionRedemptionRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('确认拒绝赎回申请')
                    ->form([
                        Textarea::make('review_remark')
                            ->label('拒绝原因')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (PositionRedemptionRequest $record, array $data): void {
                        try {
                            app(ReviewPositionRedemptionService::class)->reject(
                                (int) $record->id,
                                (int) auth()->id(),
                                $data['review_remark'] ?? null,
                            );

                            Notification::make()
                                ->title('赎回申请已拒绝')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);
                            Notification::make()
                                ->title('处理失败')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
