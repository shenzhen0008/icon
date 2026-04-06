<?php

namespace App\Modules\Position\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class PositionOrderPageController extends Controller
{
    public function __invoke(Position $position): View
    {
        Gate::authorize('view', $position);

        $position->loadMissing('product:id,name');

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

        return view('positions.show', [
            'position' => [
                'id' => $position->id,
                'product_name' => $position->product?->name ?? '--',
                'principal' => number_format((float) $position->principal, 2, '.', ''),
                'status' => $position->status,
                'opened_at' => $position->opened_at?->format('Y-m-d H:i') ?? '--',
            ],
            'daily_profits' => $dailyProfits,
            'can_apply_redemption' => $position->status === 'open',
            'redemption_request_status' => $latestRedemptionRequest?->status,
        ]);
    }
}
