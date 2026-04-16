<?php

namespace App\Filament\Resources\WithdrawalRequests\Tables;

use App\Modules\Withdrawal\Models\WithdrawalRequest;
use App\Modules\Withdrawal\Services\ReviewWithdrawalRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WithdrawalRequestsTable
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
                TextColumn::make('asset_code')
                    ->label('币种')
                    ->badge(),
                TextColumn::make('network')
                    ->label('网络'),
                TextColumn::make('destination_address')
                    ->label('收款地址')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('提款金额')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->label('提交时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('reviewer.username')
                    ->label('处理人')
                    ->placeholder('--'),
                TextColumn::make('reviewed_at')
                    ->label('处理时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('--'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('mark_processed')
                    ->label('标记已打款')
                    ->color('success')
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('review_note')
                            ->label('处理备注')
                            ->maxLength(500),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data): void {
                        app(ReviewWithdrawalRequestService::class)->markProcessed(
                            $record->id,
                            (int) auth()->id(),
                            $data['review_note'] ?? null,
                        );

                        Notification::make()
                            ->title('提款申请已标记为已打款')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_rejected')
                    ->label('驳回并退款')
                    ->color('danger')
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('review_note')
                            ->label('驳回原因')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data): void {
                        app(ReviewWithdrawalRequestService::class)->reject(
                            $record->id,
                            (int) auth()->id(),
                            $data['review_note'] ?? null,
                        );

                        Notification::make()
                            ->title('提款申请已驳回并退款')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
