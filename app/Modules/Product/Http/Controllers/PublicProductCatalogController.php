<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PublicProductCatalogController extends Controller
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
        private readonly ProductTranslationService $productTranslationService,
    ) {}

    public function __invoke(): View
    {
        $summary = [
            'today_profit' => '$ --',
            'total_profit' => '$ --',
            'orders_count' => '--',
        ];

        $user = Auth::guard('web')->user();
        if ($user !== null) {
            $todaySettledProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->whereDate('settlement_date', now()->toDateString())
                ->sum('profit');

            $totalSettledProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->sum('profit');

            $openPositions = Position::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->with(['product:id,rate_min_percent,rate_max_percent'])
                ->get(['id', 'product_id', 'principal']);

            $estimatedOpenProfit = (float) $openPositions->sum(fn (Position $position): float => $this->estimatePositionDailyProfit($position));

            $summary = [
                'today_profit' => '$'.number_format($estimatedOpenProfit, 2, '.', ''),
                'total_profit' => '$'.number_format($totalSettledProfit, 2, '.', ''),
                'orders_count' => (string) $openPositions->count(),
            ];
        } else {
            $this->temporaryAccountService->ensureGuestTempUsername(request());
        }

        $products = Product::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $this->productTranslationService->resolveName($product, emptyFallback: '--'),
                'code' => $product->code,
                'trade_mode' => $product->trade_mode,
                'unit_price' => number_format((float) $product->unit_price, 2, '.', ''),
                'purchase_limit_label' => $this->formatPurchaseLimitLabel($product->purchase_limit_count),
                'limit_range' => $this->formatRange($product->limit_min_usdt, $product->limit_max_usdt),
                'rate_range' => $this->formatPercentRange($product->rate_min_percent, $product->rate_max_percent),
                'cycle_label' => $this->formatCycleLabel($product->cycle_days),
                'product_icon_path' => $product->product_icon_path,
                'symbol_icon_paths' => $this->resolveSymbolIconPaths($product->symbol_icon_paths),
            ])
            ->all();

        return view('products.index', [
            'products' => $products,
            'summary' => $summary,
            'isGuest' => $user === null,
        ]);
    }

    private function formatRange(null|float|string $min, null|float|string $max): string
    {
        if ($min === null || $max === null) {
            return '--';
        }

        return number_format((float) $min, 0, '.', ',').'-'.number_format((float) $max, 0, '.', ',');
    }

    private function formatPercentRange(null|float|string $min, null|float|string $max): string
    {
        if ($min === null || $max === null) {
            return '--';
        }

        return number_format((float) $min, 2, '.', '').'-'.number_format((float) $max, 2, '.', '').'%';
    }

    private function formatPurchaseLimitLabel(null|int $purchaseLimitCount): string
    {
        if ($purchaseLimitCount === null) {
            return (string) __('pages/product-list.purchase_limit_unlimited');
        }

        return (string) __('pages/product-list.purchase_limit_count', [
            'count' => (string) max(0, $purchaseLimitCount),
        ]);
    }

    private function formatCycleLabel(null|int $cycleDays): string
    {
        if ($cycleDays === null) {
            return '--';
        }

        return (string) __('pages/product-list.cycle_days_format', [
            'days' => (string) max(0, $cycleDays),
        ]);
    }

    private function estimatePositionDailyProfit(Position $position): float
    {
        $product = $position->product;
        if ($product === null) {
            return 0.0;
        }

        $minPercent = $product->rate_min_percent !== null ? (float) $product->rate_min_percent : null;
        $maxPercent = $product->rate_max_percent !== null ? (float) $product->rate_max_percent : null;

        if ($minPercent === null || $maxPercent === null) {
            return 0.0;
        }

        $averageRate = (($minPercent + $maxPercent) / 2) / 100;

        return round((float) $position->principal * $averageRate, 2);
    }

    /**
     * @param mixed $rawIconPaths
     * @return array<int, string>
     */
    private function resolveSymbolIconPaths(mixed $rawIconPaths): array
    {
        $iconPaths = [];

        if (is_array($rawIconPaths)) {
            $iconPaths = $rawIconPaths;
        } elseif (is_string($rawIconPaths) && trim($rawIconPaths) !== '') {
            $decoded = json_decode($rawIconPaths, true);
            if (is_array($decoded)) {
                $iconPaths = $decoded;
            } else {
                $iconPaths = array_map('trim', explode(',', $rawIconPaths));
            }
        }

        $iconPaths = array_values(array_filter($iconPaths, static fn (mixed $path): bool => is_string($path) && trim($path) !== ''));

        if ($iconPaths !== []) {
            return $iconPaths;
        }

        return [
            '/images/products/symbols/symbol-01.png',
            '/images/products/symbols/symbol-02.png',
            '/images/products/symbols/symbol-03.png',
            '/images/products/symbols/symbol-04.png',
            '/images/products/symbols/symbol-05.png',
            '/images/products/symbols/symbol-06.png',
            '/images/products/symbols/symbol-07.png',
        ];
    }
}
