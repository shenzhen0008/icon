<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ProductRulesPageController extends Controller
{
    public function __invoke(): View
    {
        return view('products.rules');
    }
}
