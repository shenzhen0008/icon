<?php

namespace App\Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Home\Services\HomeHeroPanelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeHeroPanelFeedController extends Controller
{
    public function __construct(private readonly HomeHeroPanelService $homeHeroPanelService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['nullable', 'string', Rule::in(['demo', 'live'])],
        ]);

        $mode = (string) ($validated['mode'] ?? 'demo');
        $userId = auth('web')->id();

        if ($mode === 'live' && $userId === null) {
            return response()->json([
                'mode' => 'live',
                'badge' => '#live',
                'available_balance' => '0.00',
                'total_earnings' => '0.00',
                'earnings_24h' => '0.00',
                'trade_records' => [],
                'income_records' => [],
            ]);
        }

        return response()->json(
            $this->homeHeroPanelService->resolve($mode, $userId)
        );
    }
}
