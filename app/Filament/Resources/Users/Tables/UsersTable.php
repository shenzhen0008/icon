<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\UserProductPurchaseLimit;
use App\Modules\PopupPush\Services\PopupCampaignService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                Action::make('set_purchase_limit_override')
                    ->label('限购覆盖')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->modalHeading('设置用户产品可购次数')
                    ->form([
                        Select::make('product_id')
                            ->label('产品')
                            ->options(static fn (): array => Product::query()
                                ->orderBy('sort')
                                ->orderBy('id')
                                ->pluck('name', 'id')
                                ->mapWithKeys(static fn (mixed $name, mixed $id): array => [(string) $id => (string) $name])
                                ->all())
                            ->searchable()
                            ->required(),
                        TextInput::make('allowed_purchase_limit')
                            ->label('允许购买次数')
                            ->numeric()
                            ->rule('integer')
                            ->minValue(0)
                            ->step(1)
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        UserProductPurchaseLimit::query()->updateOrCreate(
                            [
                                'user_id' => $record->id,
                                'product_id' => (int) $data['product_id'],
                            ],
                            [
                                'allowed_purchase_limit' => max(0, (int) $data['allowed_purchase_limit']),
                                'updated_by' => auth()->id(),
                            ],
                        );

                        Notification::make()
                            ->title('限购覆盖已更新')
                            ->success()
                            ->send();
                    }),
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
