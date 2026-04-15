<?php

namespace App\Modules\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Referral\Services\GetReferralDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralDashboardController extends Controller
{
    public function __construct(private readonly GetReferralDashboardService $dashboardService)
    {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect('/me');
        }

        return view('referral.index', [
            'dashboard' => $this->dashboardService->handle($user),
        ]);
    }
}
