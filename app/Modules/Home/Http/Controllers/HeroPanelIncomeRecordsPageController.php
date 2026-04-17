<?php

namespace App\Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Home\Services\HomeHeroPanelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use stdClass;

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
        $pagination = $this->homeHeroPanelService
            ->incomeRecordQuery($user->id)
            ->paginate($perPage)
            ->withQueryString();

        $records = collect($pagination->items())
            ->map(fn (stdClass $record): array => [
                'income_type' => (string) ($record->income_type ?? ''),
                'product_name' => (string) ($record->product_name ?? '--'),
                'profit' => number_format((float) ($record->profit ?? 0), 2, '.', ''),
                'rate_percent' => $record->rate === null
                    ? '--'
                    : number_format((float) $record->rate * 100, 2, '.', '').'%',
                'settlement_at' => is_string($record->occurred_at)
                    ? $record->occurred_at
                    : '--',
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
