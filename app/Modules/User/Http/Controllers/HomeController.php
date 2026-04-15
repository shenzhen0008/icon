<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Balance\Models\RechargeReceiver;
use App\Modules\Exchange\Models\ExchangeMetric;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private readonly TemporaryAccountService $temporaryAccountService)
    {
    }

    public function __invoke(Request $request): View
    {
        $this->temporaryAccountService->ensureGuestTempUsername($request);
        $logos = config('exchange_metrics.logos', []);

        $activeMetrics = ExchangeMetric::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $metrics = $activeMetrics
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
            ->all();

        $totalProfit = (float) $activeMetrics->sum('profit_value');
        $defaultReceiver = RechargeReceiver::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->first();

        $receiverAssetCode = (string) ($defaultReceiver?->asset_code ?? '');
        $receiverNetwork = (string) ($defaultReceiver?->network ?? '');
        $receiverFallbackAddress = (string) ($defaultReceiver?->address ?? '');
        $configuredTreasuryAddress = (string) config("web3.treasury_addresses.{$receiverAssetCode}.{$receiverNetwork}", '');
        $toAddress = $configuredTreasuryAddress !== '' ? $configuredTreasuryAddress : $receiverFallbackAddress;

        return view('welcome', [
            'metrics' => $metrics,
            'summary' => [
                'participant_count' => number_format(
                    (int) $activeMetrics->sum(fn (ExchangeMetric $metric): int => $metric->btc_liquidity + $metric->eth_liquidity),
                    0,
                    '.',
                    ','
                ),
                'total_profit' => number_format($totalProfit, 2, '.', ',').' USDT',
                'earnings_24h' => '$'.number_format(round($totalProfit * 0.024, 2), 2, '.', ','),
            ],
            'paymentConfig' => [
                'token_address' => (string) config('web3.payment.token_address', ''),
                'to_address' => $toAddress,
                'asset_code' => $receiverAssetCode,
                'amount' => '10',
            ],
        ]);
    }
}
