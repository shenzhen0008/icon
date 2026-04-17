<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Services\ListUserPositionsService;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyCenterController extends Controller
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
        private readonly ListUserPositionsService $listUserPositionsService,
    )
    {
    }

    public function __invoke(Request $request): View
    {
        $user = Auth::guard('web')->user();

        if ($user !== null) {
            $positions = $this->listUserPositionsService->handle($user);

            $todayProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->whereDate('settlement_date', now()->toDateString())
                ->sum('profit');

            $totalProfit = (float) DailySettlement::query()
                ->where('user_id', $user->id)
                ->sum('profit');

            return view('me.index', [
                'isGuest' => false,
                'profile' => [
                    'label' => __('pages/me.account.formal_label'),
                    'id' => $user->username,
                    'status' => __('pages/me.account.formal_status'),
                    'created_at' => $user->created_at?->format('Y-m-d H:i') ?? '--',
                ],
                'summary' => [
                    'today_profit' => number_format($todayProfit, 2, '.', ''),
                    'total_profit' => number_format($totalProfit, 2, '.', ''),
                    'principal' => number_format((float) collect($positions)->sum(fn (array $position): float => (float) $position['principal']), 2, '.', ''),
                    'balance' => number_format((float) $user->balance, 2, '.', ''),
                ],
                'positions' => $positions,
            ]);
        }

        $temporaryUsername = $this->temporaryAccountService->ensureGuestTempUsername($request);

        return view('me.index', [
            'isGuest' => true,
            'profile' => [
                'label' => __('pages/me.account.temp_label'),
                'id' => $temporaryUsername,
                'status' => __('pages/me.account.temp_status'),
                'created_at' => '--',
            ],
            'summary' => [
                'today_profit' => '--',
                'total_profit' => '--',
                'principal' => '--',
                'balance' => '--',
            ],
            'positions' => [],
        ]);
    }
}
