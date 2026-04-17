<?php

namespace App\Modules\BankRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankRechargeEntryController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            return redirect('/login');
        }

        return redirect('/recharge/bank');
    }
}
