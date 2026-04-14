<?php

namespace App\Modules\Reservation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use App\Modules\Reservation\Http\Requests\StoreProductReservationRequest;
use App\Modules\Reservation\Services\SubmitProductReservationService;
use Illuminate\Http\RedirectResponse;

class SubmitProductReservationController extends Controller
{
    public function __construct(private readonly SubmitProductReservationService $submitProductReservationService)
    {
    }

    public function __invoke(StoreProductReservationRequest $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $this->submitProductReservationService->submit(
            $user,
            (int) $product->id,
            (float) $request->validated('amount'),
        );

        return back();
    }
}
