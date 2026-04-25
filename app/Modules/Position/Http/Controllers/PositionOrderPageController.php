<?php

namespace App\Modules\Position\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class PositionOrderPageController extends Controller
{
    public function __construct(private readonly ProductTranslationService $productTranslationService)
    {
    }

    public function __invoke(Position $position): View
    {
        Gate::authorize('view', $position);

        $position->loadMissing(['product:id,name,code,cycle_days,rate_min_percent,rate_max_percent,product_icon_path,symbol_icon_paths', 'product.translations']);

        $latestRedemptionRequest = PositionRedemptionRequest::query()
            ->where('position_id', $position->id)
            ->latest('id')
            ->first();

        $dailyProfits = DailySettlement::query()
            ->where('position_id', $position->id)
            ->orderByDesc('settlement_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (DailySettlement $settlement): array => [
                'date' => $settlement->settlement_date?->format('Y-m-d') ?? '--',
                'rate_percent' => number_format((float) $settlement->rate * 100, 2, '.', '').'%',
                'profit' => number_format((float) $settlement->profit, 2, '.', ''),
            ])
            ->all();

        $totalProfit = (float) DailySettlement::query()
            ->where('position_id', $position->id)
            ->sum('profit');

        $expireAt = null;
        $cycleDays = $position->product?->cycle_days;
        if ($position->opened_at !== null && is_numeric($cycleDays) && (int) $cycleDays > 0) {
            $expireAt = $position->opened_at->copy()->addDays((int) $cycleDays);
        }

        return view('positions.show', [
            'position' => [
                'id' => $position->id,
                'order_no' => $position->order_no ?: '--',
                'product_name' => $this->productTranslationService->resolveName($position->product, emptyFallback: '--'),
                'product_code' => $position->product?->code ?? '--',
                'principal' => number_format((float) $position->principal, 2, '.', '').'USDT',
                'rate_range' => $this->formatPercentRange($position->product?->rate_min_percent, $position->product?->rate_max_percent),
                'cycle_label' => $this->formatCycleLabel($position->product?->cycle_days),
                'total_profit' => number_format($totalProfit, 2, '.', '').'USDT',
                'status' => $position->status,
                'opened_at' => $position->opened_at?->translatedFormat('F j, Y g:i:s A') ?? '--',
                'expire_at' => $expireAt?->translatedFormat('F j, Y g:i:s A') ?? '--',
                'product_icon_path' => $position->product?->product_icon_path,
                'symbol_icon_paths' => $this->resolveSymbolIconPaths($position->product?->symbol_icon_paths),
            ],
            'daily_profits' => $dailyProfits,
            // Temporarily hide redemption entry on position detail page.
            'can_apply_redemption' => false,
            'redemption_request_status' => $latestRedemptionRequest?->status,
        ]);
    }

    private function formatPercentRange(null|float|string $min, null|float|string $max): string
    {
        if ($min === null || $max === null) {
            return '--';
        }

        return number_format((float) $min, 2, '.', '').'-'.number_format((float) $max, 2, '.', '').'%';
    }

    private function formatCycleLabel(null|int $cycleDays): string
    {
        if ($cycleDays === null) {
            return '--';
        }

        return (string) __('pages/positions.cycle_days_format', [
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
