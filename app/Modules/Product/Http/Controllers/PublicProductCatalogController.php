<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PublicProductCatalogController extends Controller
{
    public function __invoke(): View
    {
        $summary = [
            'today_profit' => '$ --',
            'total_profit' => '$ --',
            'orders_count' => '--',
        ];

        $user = Auth::guard('web')->user();
        if ($user !== null) {
            $todayProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->whereDate('settlement_date', now()->toDateString())
                ->sum('profit');

            $totalProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->sum('profit');

            $ordersCount = Position::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->count();

            $summary = [
                'today_profit' => '$'.number_format($todayProfit, 2, '.', ''),
                'total_profit' => '$'.number_format($totalProfit, 2, '.', ''),
                'orders_count' => (string) $ordersCount,
            ];
        }

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'trade_mode' => $product->trade_mode,
                'unit_price' => number_format((float) $product->unit_price, 2, '.', ''),
                'limit_range' => $this->formatRange($product->limit_min_usdt, $product->limit_max_usdt),
                'rate_range' => $this->formatPercentRange($product->rate_min_percent, $product->rate_max_percent),
                'cycle_label' => $product->cycle_days === null ? '--' : $product->cycle_days.'天',
                'product_icon_path' => $product->product_icon_path,
                'symbol_icon_paths' => $this->resolveSymbolIconPaths($product->symbol_icon_paths),
            ])
            ->all();

        return view('products.index', [
            'products' => $products,
            'summary' => $summary,
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
