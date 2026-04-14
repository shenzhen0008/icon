<?php

namespace App\Modules\Position\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Services\ListUserPositionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PositionOrdersPageController extends Controller
{
    public function __construct(private readonly ListUserPositionsService $listUserPositionsService)
    {
    }

    public function __invoke(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();

        return view('orders.index', [
            'positions' => $this->listUserPositionsService->handle($user),
        ]);
    }
}
