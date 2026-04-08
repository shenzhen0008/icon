<?php

namespace App\Filament\Resources\RechargePaymentRequests\Tables;

use App\Modules\Balance\Models\RechargePaymentRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class RechargePaymentRequestsTable
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
                TextColumn::make('contact_account')
                    ->label('联系账号')
                    ->searchable(),
                TextColumn::make('asset_code')
                    ->label('币种')
                    ->badge(),
                TextColumn::make('payment_amount')
                    ->label('付款金额')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('network')
                    ->label('网络')
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('receipt_image_path')
                    ->label('付款截图')
                    ->disk('public')
                    ->url(
                        fn (RechargePaymentRequest $record): string => Storage::disk('public')->url((string) $record->receipt_image_path),
                        shouldOpenInNewTab: true,
                    )
                    ->square(),
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
                    ->label('标记已处理')
                    ->color('success')
                    ->visible(fn (RechargePaymentRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('review_note')
                            ->label('处理备注')
                            ->maxLength(500),
                    ])
                    ->action(function (RechargePaymentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'processed',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('充值申请已标记为已处理')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_rejected')
                    ->label('标记驳回')
                    ->color('danger')
                    ->visible(fn (RechargePaymentRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('review_note')
                            ->label('驳回原因')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (RechargePaymentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('充值申请已驳回')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
