<?php

namespace App\Modules\Exchange\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exchange\Models\ExchangeMetric;
use App\Modules\Exchange\Services\ExchangeMetricsTickService;
use Illuminate\Http\JsonResponse;

class ExchangeMetricsFeedController extends Controller
{
    public function __construct(private readonly ExchangeMetricsTickService $tickService)
    {
    }

    public function __invoke(): JsonResponse
    {
        if (! app()->environment('testing')) {
            $this->tickService->tickActive();
        }
        $logos = config('exchange_metrics.logos', []);

        $data = ExchangeMetric::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (ExchangeMetric $metric): array => [
                'exchange_code' => $metric->exchange_code,
                'exchange_name' => $metric->exchange_name,
                'logo_url' => (string) ($logos[$metric->exchange_code] ?? ''),
                'btc_value' => '$'.number_format((float) $metric->btc_value, 2, '.', ','),
                'btc_liquidity' => (string) $metric->btc_liquidity,
                'eth_value' => '$'.number_format((float) $metric->eth_value, 2, '.', ','),
                'eth_liquidity' => (string) $metric->eth_liquidity,
                'profit_value' => number_format((float) $metric->profit_value, 2, '.', ','),
                'updated_at' => $metric->updated_at?->toDateTimeString() ?? '--',
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'server_time' => now()->toDateTimeString(),
        ]);
    }
}
