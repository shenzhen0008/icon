<?php

namespace App\Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Home\Services\HomeHeroPanelService;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class HeroPanelIncomeRecordsPageController extends Controller
{
    public function __construct(private readonly HomeHeroPanelService $homeHeroPanelService)
    {
    }

    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'mode' => ['nullable', 'string', Rule::in(['demo', 'live'])],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);
        $mode = (string) ($validated['mode'] ?? 'live');
        /** @var \App\Models\User $user */
        $user = auth('web')->user();

        if ($mode === 'demo') {
            $panel = $this->homeHeroPanelService->resolve('demo');
            $records = (array) ($panel['income_records'] ?? []);

            return view('home.hero-panel-income-records', [
                'records' => $records,
                'mode' => $mode,
                'pagination' => null,
            ]);
        }

        /** @var LengthAwarePaginator $pagination */
        $pagination = DailySettlement::query()
            ->with('product:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $records = collect($pagination->items())
            ->map(fn (DailySettlement $settlement): array => [
                'product_name' => (string) ($settlement->product?->name ?? '--'),
                'profit' => number_format((float) $settlement->profit, 2, '.', ''),
                'rate_percent' => number_format((float) $settlement->rate * 100, 2, '.', '').'%',
                'settlement_at' => $settlement->created_at?->format('Y-m-d H:i:s') ?? '--',
            ])
            ->values()
            ->all();

        return view('home.hero-panel-income-records', [
            'records' => $records,
            'mode' => $mode,
            'pagination' => $pagination,
        ]);
    }
}
