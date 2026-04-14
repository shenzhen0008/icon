<?php

namespace App\Filament\Resources\ProductReservations\Tables;

use App\Modules\Reservation\Models\ProductReservation;
use App\Modules\Reservation\Services\ReviewProductReservationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class ProductReservationsTable
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
                TextColumn::make('product.name')
                    ->label('商品')
                    ->searchable(),
                TextColumn::make('amount_usdt')
                    ->label('金额(USDT)')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge(),
                TextColumn::make('created_at')
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
                    ->visible(fn (ProductReservation $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('确认通过预订申请')
                    ->form([
                        Textarea::make('review_note')
                            ->label('审批备注')
                            ->maxLength(500),
                    ])
                    ->action(function (ProductReservation $record, array $data): void {
                        try {
                            app(ReviewProductReservationService::class)->approve(
                                (int) $record->id,
                                auth()->id(),
                                $data['review_note'] ?? null,
                            );

                            Notification::make()
                                ->title('预订申请已转正式订单')
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
                    ->visible(fn (ProductReservation $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('确认拒绝预订申请')
                    ->form([
                        Textarea::make('review_note')
                            ->label('拒绝原因')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (ProductReservation $record, array $data): void {
                        try {
                            app(ReviewProductReservationService::class)->reject(
                                (int) $record->id,
                                auth()->id(),
                                $data['review_note'] ?? null,
                            );

                            Notification::make()
                                ->title('预订申请已拒绝')
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
