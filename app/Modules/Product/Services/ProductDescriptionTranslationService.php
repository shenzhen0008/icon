<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Models\Product;

class ProductDescriptionTranslationService
{
    public function __construct(private readonly ProductTranslationService $productTranslationService)
    {
    }

    public function resolveDescription(Product $product, ?string $currentLocale = null): string
    {
        return $this->productTranslationService->resolveDescription($product, $currentLocale);
    }
}
