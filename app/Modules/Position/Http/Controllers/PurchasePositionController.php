<?php

namespace App\Modules\Position\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Position\Http\Requests\PurchasePositionRequest;
use App\Modules\Position\Services\PurchasePositionService;
use Illuminate\Http\RedirectResponse;

class PurchasePositionController extends Controller
{
    public function __construct(private readonly PurchasePositionService $purchasePositionService)
    {
    }

    public function __invoke(PurchasePositionRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $this->purchasePositionService->purchase(
            $user,
            (int) $request->validated('product_id'),
            (int) $request->validated('shares'),
        );

        return back();
    }
}
