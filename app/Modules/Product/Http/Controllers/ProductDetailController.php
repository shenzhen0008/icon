<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function __invoke(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();
        $balance = $user === null ? null : (float) $user->balance;

        return view('products.show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'unit_price' => number_format((float) $product->unit_price, 2, '.', ''),
            ],
            'isGuest' => $user === null,
            'balance' => $balance === null ? null : number_format($balance, 2, '.', ''),
        ]);
    }
}
