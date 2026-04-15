<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Balance\Models\RechargeReceiver;
use App\Modules\Exchange\Models\ExchangeMetric;
use App\Modules\Home\Models\HomeDisplaySetting;
use App\Modules\Home\Services\HomeSummaryService;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
        private readonly HomeSummaryService $homeSummaryService,
    )
    {
    }

    public function __invoke(Request $request): View
    {
        $this->temporaryAccountService->ensureGuestTempUsername($request);
        $summary = $this->homeSummaryService->resolve();
        $homeDisplaySetting = HomeDisplaySetting::query()->findOrFail(1);
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
                'btc_value' => (string) $metric->display_btc_volume,
                'btc_liquidity' => (string) $metric->display_btc_liquidity,
                'eth_value' => (string) $metric->display_eth_volume,
                'eth_liquidity' => (string) $metric->display_eth_liquidity,
                'profit_value' => number_format((float) $homeDisplaySetting->shared_exchange_profit_base_value, 2, '.', ','),
            ])
            ->all();
        $tokenContracts = (array) config('web3.token_contracts', []);
        $homeQuickPayAssets = (array) config('web3.home_quick_pay_assets', ['USDT']);
        $walletChainId = (string) config('web3.payment.chain_id', config('web3.default_chain_id', '56'));
        $defaultAmount = '10';

        $homePaymentAssets = RechargeReceiver::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(function (RechargeReceiver $receiver) use ($tokenContracts, $walletChainId): ?array {
                $assetCode = strtoupper((string) $receiver->asset_code);
                $network = $this->normalizeNetwork((string) $receiver->network);
                if (! in_array($network, ['BSC', 'ETH'], true)) {
                    return null;
                }

                $tokenAddress = (string) ($tokenContracts[$assetCode][$network] ?? '');
                if (! $this->isEvmAddress($tokenAddress)) {
                    return null;
                }

                $configuredToAddress = (string) config("web3.treasury_addresses.{$assetCode}.{$network}", '');
                $fallbackToAddress = (string) $receiver->address;
                $toAddress = $this->isEvmAddress($configuredToAddress)
                    ? $configuredToAddress
                    : ($this->isEvmAddress($fallbackToAddress) ? $fallbackToAddress : '');

                if ($toAddress === '') {
                    return null;
                }

                return [
                    'code' => $assetCode,
                    'network' => $network,
                    'token_address' => $tokenAddress,
                    'to_address' => $toAddress,
                    'chain_id' => $walletChainId,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (count($homeQuickPayAssets) > 0) {
            $homePaymentAssets = array_values(array_filter(
                $homePaymentAssets,
                static fn (array $asset): bool => in_array(strtoupper((string) ($asset['code'] ?? '')), $homeQuickPayAssets, true)
            ));
        }

        if (count($homePaymentAssets) === 0) {
            $fallbackAssetCodes = count($homeQuickPayAssets) > 0
                ? $homeQuickPayAssets
                : (array) config('web3.supported_assets', ['USDT']);
            foreach ($fallbackAssetCodes as $assetCodeRaw) {
                $assetCode = strtoupper((string) $assetCodeRaw);
                $fallbackTokenAddress = (string) ($tokenContracts[$assetCode]['BSC'] ?? '');
                $fallbackTreasuryAddress = (string) config("web3.treasury_addresses.{$assetCode}.BSC", '');
                $fallbackToAddress = $this->isEvmAddress($fallbackTreasuryAddress)
                    ? $fallbackTreasuryAddress
                    : (string) config('web3.payment.to_address', '');

                if ($this->isEvmAddress($fallbackTokenAddress) && $this->isEvmAddress($fallbackToAddress)) {
                    $homePaymentAssets[] = [
                        'code' => $assetCode,
                        'network' => 'BSC',
                        'token_address' => $fallbackTokenAddress,
                        'to_address' => $fallbackToAddress,
                        'chain_id' => $walletChainId,
                    ];
                }
            }

            if (count($homePaymentAssets) === 0) {
                $fallbackTokenAddress = (string) config('web3.payment.token_address', '');
                $fallbackToAddress = (string) config('web3.payment.to_address', '');
                if ($this->isEvmAddress($fallbackTokenAddress) && $this->isEvmAddress($fallbackToAddress)) {
                    $homePaymentAssets[] = [
                        'code' => 'USDT',
                        'network' => 'BSC',
                        'token_address' => $fallbackTokenAddress,
                        'to_address' => $fallbackToAddress,
                        'chain_id' => $walletChainId,
                    ];
                }
            }
        }

        $defaultHomeAsset = $homePaymentAssets[0] ?? null;

        return view('welcome', [
            'isGuest' => ! Auth::guard('web')->check(),
            'metrics' => $metrics,
            'summary' => $summary,
            'sharedExchangeProfit' => [
                'base_value' => number_format((float) $homeDisplaySetting->shared_exchange_profit_base_value, 2, '.', ''),
                'step_seconds' => $homeDisplaySetting->shared_exchange_profit_step_seconds,
                'min_delta' => number_format((float) $homeDisplaySetting->shared_exchange_profit_min_delta, 2, '.', ''),
                'max_delta' => number_format((float) $homeDisplaySetting->shared_exchange_profit_max_delta, 2, '.', ''),
            ],
            'homePaymentAssets' => $homePaymentAssets,
            'paymentConfig' => [
                'token_address' => (string) ($defaultHomeAsset['token_address'] ?? config('web3.payment.token_address', '')),
                'to_address' => (string) ($defaultHomeAsset['to_address'] ?? config('web3.payment.to_address', '')),
                'asset_code' => (string) ($defaultHomeAsset['code'] ?? ''),
                'amount' => $defaultAmount,
                'chain_id' => (string) ($defaultHomeAsset['chain_id'] ?? $walletChainId),
            ],
        ]);
    }

    private function normalizeNetwork(string $network): string
    {
        $normalized = strtoupper(trim($network));
        return match ($normalized) {
            'BSC', 'BNB SMART CHAIN' => 'BSC',
            'ETH', 'ETHEREUM' => 'ETH',
            default => $normalized,
        };
    }

    private function isEvmAddress(string $value): bool
    {
        return (bool) preg_match('/^0x[a-fA-F0-9]{40}$/', $value);
    }

}
