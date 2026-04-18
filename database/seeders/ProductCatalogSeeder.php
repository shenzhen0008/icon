<?php

namespace Database\Seeders;

use App\Modules\Product\Models\Product;
use Illuminate\Database\Seeder;
use Throwable;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $products = $this->loadProducts();

        foreach ($products as $item) {
            if (! is_array($item) || ! isset($item['code'])) {
                continue;
            }

            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            unset($item['translations']);

            $product = Product::query()->updateOrCreate(
                ['code' => (string) $item['code']],
                $item
            );

            foreach ($translations as $locale => $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                $product->translations()->updateOrCreate(
                    ['locale' => (string) $locale],
                    [
                        'title' => $translation['title'] ?? null,
                        'description' => (string) ($translation['description'] ?? ''),
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadProducts(): array
    {
        $file = database_path('seeders/data/product_catalog.json');

        if (! is_file($file)) {
            return [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
