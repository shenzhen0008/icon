<?php

namespace App\Filament\Resources\SavingsYieldSettings\Pages;

use App\Filament\Resources\SavingsYieldSettings\SavingsYieldSettingResource;
use App\Modules\Savings\Models\SavingsYieldSetting;
use App\Modules\Settlement\Services\RunDailyIncomeSettlementService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditSavingsYieldSetting extends EditRecord
{
    protected static string $resource = SavingsYieldSettingResource::class;

    public function mount(int|string $record): void
    {
        SavingsYieldSetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'daily_rate' => '0.0000',
            'is_active' => false,
        ]);

        parent::mount(1);
    }

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
            DeleteAction::make()->visible(false),
        ];
    }
}
