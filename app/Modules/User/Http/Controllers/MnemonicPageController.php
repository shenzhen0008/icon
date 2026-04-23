<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MnemonicPageController extends Controller
{
    public function __invoke(): View
    {
        return view('me.mnemonic');
    }
}
