<?php

namespace Tests\Feature\Exchange;

use App\Modules\Exchange\Models\ExchangeMetric;
use App\Modules\Exchange\Services\ExchangeMetricsTickService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExchangeMetricsTickServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tick_active_updates_profit_within_negative_zero_point_zero_five_to_positive_zero_point_two_percent(): void
    {
        Cache::forget('exchange_metrics:liquidity_next_at');

        $metric = ExchangeMetric::query()->where('exchange_code', 'binance')->firstOrFail();
        $metric->update(['profit_value' => 2000.00]);

        app(ExchangeMetricsTickService::class)->tickActive();

        $metric->refresh();

        $delta = round((float) $metric->profit_value - 2000.00, 2);

        $this->assertGreaterThanOrEqual(-1.00, $delta);
        $this->assertLessThanOrEqual(4.00, $delta);
    }

    public function test_tick_active_updates_liquidity_for_two_or_three_platforms(): void
    {
        Cache::forget('exchange_metrics:liquidity_next_at');

        $before = ExchangeMetric::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'btc_liquidity', 'eth_liquidity'])
            ->keyBy('id');

        app(ExchangeMetricsTickService::class)->tickActive();

        $after = ExchangeMetric::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'btc_liquidity', 'eth_liquidity']);

        $changedPlatformCount = $after
            ->filter(function (ExchangeMetric $metric) use ($before): bool {
                $original = $before->get($metric->id);

                return $original !== null
                    && (
                        (int) $original->btc_liquidity !== (int) $metric->btc_liquidity
                        || (int) $original->eth_liquidity !== (int) $metric->eth_liquidity
                    );
            })
            ->count();

        $this->assertGreaterThanOrEqual(2, $changedPlatformCount);
        $this->assertLessThanOrEqual(3, $changedPlatformCount);
    }
}
