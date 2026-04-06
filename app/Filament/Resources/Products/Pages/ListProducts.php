<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Modules\Settlement\Services\DailySettlementService;
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
                ->modalDescription('将对所有产品的全部持仓订单执行今日结算。重复执行不会重复入账。')
                ->action(function (): void {
                    try {
                        app(DailySettlementService::class)->settleAllProductsByDate(now()->toDateString());

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
