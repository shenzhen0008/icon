<?php

namespace App\Modules\Home\Services;

use App\Modules\Home\Models\HomeDisplaySetting;
use App\Modules\PopupPush\Services\PopupFeedService;

class HomeSummaryService
{
    public function __construct(
        private readonly DynamicDisplayValueService $dynamicDisplayValueService,
        private readonly PopupFeedService $popupFeedService,
    )
    {
    }

    public function resolve(?int $userId = null): array
    {
        $setting = HomeDisplaySetting::query()->firstOrCreate([
            'id' => 1,
        ], [
            'summary_people_count' => '0',
            'summary_people_step_seconds' => 3,
            'summary_people_min_delta' => 0,
            'summary_people_max_delta' => 0,
            'summary_total_profit' => '0.00',
            'summary_profit_step_seconds' => 3,
            'summary_profit_min_delta' => 0,
            'summary_profit_max_delta' => 0,
        ]);

        $this->tick($setting);

        return [
            'participant_count' => $this->formatInteger($setting->summary_people_count),
            'total_profit' => $this->formatMoney($setting->summary_total_profit),
            'participant_ticker' => [
                'base_value' => (string) (int) round((float) $setting->summary_people_count),
                'step_seconds' => (int) $setting->summary_people_step_seconds,
                'min_delta' => (string) (float) $setting->summary_people_min_delta,
                'max_delta' => (string) (float) $setting->summary_people_max_delta,
            ],
            'profit_ticker' => [
                'base_value' => number_format((float) $setting->summary_total_profit, 2, '.', ''),
                'step_seconds' => (int) $setting->summary_profit_step_seconds,
                'min_delta' => number_format((float) $setting->summary_profit_min_delta, 2, '.', ''),
                'max_delta' => number_format((float) $setting->summary_profit_max_delta, 2, '.', ''),
            ],
            'popup' => $this->popupFeedService->resolveForUser($userId),
        ];
    }

    private function tick(HomeDisplaySetting $setting): void
    {
        $people = $this->dynamicDisplayValueService->tickValue(
            currentValue: (float) $setting->summary_people_count,
            lastTickAt: $setting->summary_people_last_tick_at,
            stepSeconds: $setting->summary_people_step_seconds,
            minDelta: (float) $setting->summary_people_min_delta,
            maxDelta: (float) $setting->summary_people_max_delta,
            precision: 0,
        );

        $profit = $this->dynamicDisplayValueService->tickValue(
            currentValue: (float) $setting->summary_total_profit,
            lastTickAt: $setting->summary_profit_last_tick_at,
            stepSeconds: $setting->summary_profit_step_seconds,
            minDelta: (float) $setting->summary_profit_min_delta,
            maxDelta: (float) $setting->summary_profit_max_delta,
            precision: 2,
        );

        $updates = [];

        if ($people['did_tick']) {
            $updates['summary_people_count'] = (string) (int) round($people['current_value']);
            $updates['summary_people_last_tick_at'] = $people['last_tick_at'];
            $setting->summary_people_count = $updates['summary_people_count'];
            $setting->summary_people_last_tick_at = $people['last_tick_at'];
        }

        if ($profit['did_tick']) {
            $updates['summary_total_profit'] = number_format((float) $profit['current_value'], 2, '.', '');
            $updates['summary_profit_last_tick_at'] = $profit['last_tick_at'];
            $setting->summary_total_profit = $updates['summary_total_profit'];
            $setting->summary_profit_last_tick_at = $profit['last_tick_at'];
        }

        if ($updates !== []) {
            $setting->forceFill($updates)->save();
        }
    }

    private function formatMoney(string $value): string
    {
        return $this->dynamicDisplayValueService->format($value, 'money_2');
    }

    private function formatInteger(string $value): string
    {
        return $this->dynamicDisplayValueService->format($value, 'integer');
    }
}
