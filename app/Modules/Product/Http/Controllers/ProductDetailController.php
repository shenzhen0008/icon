<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
        private readonly ProductTranslationService $productTranslationService,
    ) {}

    public function __invoke(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();
        $balance = $user === null ? null : (float) $user->balance;

        if ($user === null) {
            $this->temporaryAccountService->ensureGuestTempUsername($request);
        }

        $product->loadMissing('translations');

        return view('products.show', [
            'product' => [
                'id' => $product->id,
                'name' => $this->productTranslationService->resolveName($product, emptyFallback: '--'),
                'code' => $product->code,
                'trade_mode' => $product->trade_mode,
                'unit_price' => number_format((float) $product->unit_price, 2, '.', ''),
                'description' => $this->productTranslationService->resolveDescription($product),
                'limit_range' => $this->formatRange($product->limit_min_usdt, $product->limit_max_usdt),
                'limit_max_amount' => $this->formatInputAmount($product->limit_max_usdt),
                'rate_range' => $this->formatPercentRange($product->rate_min_percent, $product->rate_max_percent),
                'cycle_label' => $this->formatCycleLabel($product->cycle_days),
                'product_icon_path' => $product->product_icon_path,
                'symbol_icon_paths' => $this->resolveSymbolIconPaths($product->symbol_icon_paths),
            ],
            'isGuest' => $user === null,
            'balance' => $balance === null ? null : number_format($balance, 2, '.', ''),
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

    private function formatInputAmount(null|float|string $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        $formatted = number_format((float) $amount, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function formatCycleLabel(null|int $cycleDays): string
    {
        if ($cycleDays === null) {
            return '--';
        }

        return (string) __('pages/product-detail.cycle_days_format', [
            'days' => (string) max(0, $cycleDays),
        ]);
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
