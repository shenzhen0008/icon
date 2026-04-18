<?php

namespace App\Modules\Product\Services;

use App\Modules\I18n\Services\TranslationFallbackResolver;
use App\Modules\Product\Models\Product;

class ProductTranslationService
{
    public function __construct(private readonly TranslationFallbackResolver $resolver)
    {
    }

    public function resolveName(null|Product $product, ?string $currentLocale = null, string $emptyFallback = ''): string
    {
        if (! $product instanceof Product) {
            return $emptyFallback;
        }

        $title = $this->resolver->resolveField(
            $product->translations,
            'title',
            $currentLocale,
        );

        if (is_string($title) && trim($title) !== '') {
            return $title;
        }

        $name = $product->name;

        if (is_string($name) && trim($name) !== '') {
            return $name;
        }

        return $emptyFallback;
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
