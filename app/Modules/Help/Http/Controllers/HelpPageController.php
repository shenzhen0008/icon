<?php

namespace App\Modules\Help\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class HelpPageController extends Controller
{
    public function __invoke(): View
    {
        return view('help.index', [
            'faqs' => config('help.faqs', []),
        ]);
    }
}
