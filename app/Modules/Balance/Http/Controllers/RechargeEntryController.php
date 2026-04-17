<?php

namespace App\Modules\Balance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RechargeEntryController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $method = (string) $request->query('payment-method', 'crypto');

        if ($method === 'bank-card') {
            if (! Auth::guard('web')->check()) {
                return redirect('/login');
            }

            return redirect('/recharge/bank/entry');
        }

        return redirect('/recharge');
    }
}

