<?php

namespace App\Modules\Reservation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reservation\Models\ProductReservation;
use Illuminate\Http\RedirectResponse;

class CancelProductReservationController extends Controller
{
    public function __invoke(ProductReservation $reservation): RedirectResponse
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        if ((int) $reservation->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($reservation->status === 'pending') {
            $reservation->status = 'cancelled';
            $reservation->save();
        }

        return back();
    }
}
