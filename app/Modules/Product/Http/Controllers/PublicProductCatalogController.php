<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use Illuminate\Contracts\View\View;

class PublicProductCatalogController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'unit_price' => number_format((float) $product->unit_price, 2, '.', ''),
            ])
            ->all();

        return view('products.index', [
            'products' => $products,
        ]);
    }
}
