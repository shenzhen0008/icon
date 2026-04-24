<?php

namespace App\Modules\ClientEnv\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ClientEnvAccessReminderPageController extends Controller
{
    public function __invoke(): View
    {
        return view('client-env.access-reminder', [
            'homeUrl' => url('/'),
        ]);
    }
}

