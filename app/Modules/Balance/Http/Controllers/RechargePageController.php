<?php

namespace App\Modules\Balance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class RechargePageController extends Controller
{
    public function __invoke(): View
    {
        return view('recharge.index');
    }
}
