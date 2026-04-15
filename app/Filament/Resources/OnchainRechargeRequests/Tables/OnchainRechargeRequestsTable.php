<?php

namespace App\Filament\Resources\OnchainRechargeRequests\Tables;

use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\OnchainRecharge\Services\ReviewOnchainRechargeRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OnchainRechargeRequestsTable
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
                TextColumn::make('payment_amount')
                    ->label('金额')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('network')
                    ->label('网络')
                    ->badge(),
                TextColumn::make('tx_hash')
                    ->label('交易哈希')
                    ->searchable()
                    ->limit(18),
                TextColumn::make('chain_id')
                    ->label('链ID'),
                TextColumn::make('from_address')
                    ->label('付款地址')
                    ->limit(18),
                TextColumn::make('to_address')
                    ->label('收款地址')
                    ->limit(18),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->label('提交时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('mark_processed')
                    ->label('确认入账')
                    ->color('success')
                    ->visible(fn (RechargePaymentRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('review_note')
                            ->label('处理备注')
                            ->maxLength(500),
                    ])
                    ->action(function (RechargePaymentRequest $record, array $data): void {
                        app(ReviewOnchainRechargeRequestService::class)->markProcessed(
                            $record->id,
                            (int) auth()->id(),
                            $data['review_note'] ?? null,
                        );

                        Notification::make()
                            ->title('链上充值已确认入账')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_rejected')
                    ->label('驳回')
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
                        app(ReviewOnchainRechargeRequestService::class)->reject(
                            $record->id,
                            (int) auth()->id(),
                            $data['review_note'] ?? null,
                        );

                        Notification::make()
                            ->title('链上充值申请已驳回')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
