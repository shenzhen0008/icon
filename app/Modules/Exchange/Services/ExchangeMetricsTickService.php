<?php

namespace App\Modules\Exchange\Services;

use App\Modules\Exchange\Models\ExchangeMetric;
use Illuminate\Support\Facades\Cache;

class ExchangeMetricsTickService
{
    private const LIQUIDITY_NEXT_AT_CACHE_KEY = 'exchange_metrics:liquidity_next_at';

    public function tickActive(): void
    {
        $metrics = ExchangeMetric::query()
            ->where('is_active', true)
            ->get();

        foreach ($metrics as $metric) {
            $profit = (float) $metric->profit_value;
            $nextProfit = max(0, round($profit + $this->randomDelta($profit), 2));

            $metric->update([
                'profit_value' => $nextProfit,
                'updated_at' => now(),
            ]);
        }

        $this->tickParticipantCount($metrics);
    }

    private function randomDelta(float $base): float
    {
        $effectiveBase = max($base, 1.0);
        $ratio = random_int(-20, 20) / 10000; // -0.20% ~ +0.20%

        return $effectiveBase * $ratio;
    }

    /**
     * Keep participant changes slow: every 3-8 seconds, and each change is +/-1~2.
     */
    private function tickParticipantCount($metrics): void
    {
        if ($metrics->isEmpty()) {
            return;
        }

        $now = now();
        $nextAt = Cache::get(self::LIQUIDITY_NEXT_AT_CACHE_KEY);

        if (is_string($nextAt) && $now->lt($nextAt)) {
            return;
        }

        /** @var ExchangeMetric $target */
        $target = $metrics->random();
        $field = random_int(0, 1) === 0 ? 'btc_liquidity' : 'eth_liquidity';
        $delta = random_int(1, 2) * (random_int(0, 1) === 0 ? -1 : 1);
        $current = (int) $target->{$field};
        $next = max(0, $current + $delta);

        $target->update([
            $field => $next,
            'updated_at' => now(),
        ]);

        Cache::put(
            self::LIQUIDITY_NEXT_AT_CACHE_KEY,
            $now->copy()->addSeconds(random_int(3, 8))->toDateTimeString(),
            now()->addMinutes(30)
        );
    }
}
