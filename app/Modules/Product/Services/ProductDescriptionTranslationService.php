<?php

namespace App\Modules\Product\Services;

use App\Modules\I18n\Services\TranslationFallbackResolver;
use App\Modules\Product\Models\Product;

class ProductDescriptionTranslationService
{
    public function __construct(private readonly TranslationFallbackResolver $resolver)
    {
    }

    public function resolveDescription(Product $product, ?string $currentLocale = null): string
    {
        $description = $this->resolver->resolveField(
            $product->translations,
            'description',
            $currentLocale,
        );

        return is_string($description) ? $description : '';
    }
}
