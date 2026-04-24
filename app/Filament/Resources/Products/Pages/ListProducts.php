<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Modules\Settlement\Services\RunDailyIncomeSettlementService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settleTodayAll')
                ->label('手动触发当日结算')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('确认执行当日全量结算')
                ->modalDescription('将执行今日产品收益结算、储蓄收益结算与推荐收益处理。重复执行不会重复入账。')
                ->action(function (): void {
                    try {
                        $date = now((string) config('settlement.timezone', 'Asia/Shanghai'))->toDateString();

                        $result = app(RunDailyIncomeSettlementService::class)->handle($date);

                        if (! $result['lock_acquired']) {
                            Notification::make()
                                ->title('结算任务正在执行')
                                ->body((string) $result['message'])
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('当日结算已完成')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('结算执行失败')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
