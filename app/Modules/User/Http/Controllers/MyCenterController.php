<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Settlement\Models\DailySettlement;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyCenterController extends Controller
{
    public function __construct(private readonly TemporaryAccountService $temporaryAccountService)
    {
    }

    public function __invoke(Request $request): View
    {
        $user = Auth::guard('web')->user();

        if ($user !== null) {
            $positions = Position::query()
                ->with('product:id,name')
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('id')
                ->get();

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
                    'label' => '正式账号',
                    'id' => $user->username,
                    'status' => '已登录',
                    'created_at' => $user->created_at?->format('Y-m-d H:i') ?? '--',
                ],
                'summary' => [
                    'today_profit' => number_format($todayProfit, 2, '.', ''),
                    'total_profit' => number_format($totalProfit, 2, '.', ''),
                    'principal' => number_format((float) $positions->sum('principal'), 2, '.', ''),
                    'balance' => number_format((float) $user->balance, 2, '.', ''),
                ],
                'positions' => $positions->map(fn (Position $position): array => [
                    'name' => $position->product?->name ?? '--',
                    'principal' => number_format((float) $position->principal, 2, '.', ''),
                    'status' => $position->status,
                ])->all(),
            ]);
        }

        $temporaryUsername = $this->temporaryAccountService->ensureGuestTempUsername($request);

        return view('me.index', [
            'isGuest' => true,
            'profile' => [
                'label' => '临时账号',
                'id' => $temporaryUsername,
                'status' => '访客未注册',
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
