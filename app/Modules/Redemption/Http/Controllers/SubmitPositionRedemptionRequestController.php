<?php

namespace App\Modules\Redemption\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Models\Position;
use App\Modules\Redemption\Http\Requests\StorePositionRedemptionRequest;
use App\Modules\Redemption\Services\SubmitPositionRedemptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SubmitPositionRedemptionRequestController extends Controller
{
    public function __construct(private readonly SubmitPositionRedemptionService $submitPositionRedemptionService)
    {
    }

    public function __invoke(StorePositionRedemptionRequest $request, Position $position): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        Gate::authorize('view', $position);

        $this->submitPositionRedemptionService->submit($user, (int) $position->id);

        return back();
    }
}
