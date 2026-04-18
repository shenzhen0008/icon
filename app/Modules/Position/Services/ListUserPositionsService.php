<?php

namespace App\Modules\Position\Services;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Services\ProductTranslationService;
use App\Modules\Settlement\Models\DailySettlement;

class ListUserPositionsService
{
    public function __construct(private readonly ProductTranslationService $productTranslationService)
    {
    }

    /**
     * @return array<int, array{
     *     id:int,
     *     name:string,
     *     principal:string,
     *     status:string,
     *     recent_profits:array<int, array{date:string, profit:string}>
     * }>
     */
    public function handle(User $user): array
    {
        $positions = Position::query()
            ->with(['product:id,name', 'product.translations'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['open', 'redeeming'])
            ->latest('id')
            ->get();

        $recentProfitRowsByPosition = DailySettlement::query()
            ->whereIn('position_id', $positions->pluck('id'))
            ->orderByDesc('settlement_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('position_id')
            ->map(fn ($rows): array => $rows
                ->take(3)
                ->map(fn (DailySettlement $settlement): array => [
                    'date' => $settlement->settlement_date?->format('m-d') ?? '--',
                    'profit' => number_format((float) $settlement->profit, 2, '.', ''),
                ])
                ->values()
                ->all())
            ->all();

        return $positions->map(fn (Position $position): array => [
            'id' => $position->id,
            'name' => $this->productTranslationService->resolveName($position->product, emptyFallback: '--'),
            'principal' => number_format((float) $position->principal, 2, '.', ''),
            'status' => $position->status,
            'recent_profits' => $recentProfitRowsByPosition[$position->id] ?? [],
        ])->all();
    }
}
