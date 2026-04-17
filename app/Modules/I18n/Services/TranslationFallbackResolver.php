<?php

namespace App\Modules\I18n\Services;

use Illuminate\Support\Facades\App;

class TranslationFallbackResolver
{
    /**
     * @return array<int, string>
     */
    public function preferredLocales(?string $currentLocale = null): array
    {
        $resolvedCurrentLocale = $currentLocale ?? App::currentLocale();
        $fallbackLocale = (string) config('i18n.fallback_locale', config('app.fallback_locale'));

        return array_values(array_unique(array_filter([
            $resolvedCurrentLocale,
            $fallbackLocale,
        ], static fn (mixed $locale): bool => is_string($locale) && $locale !== '')));
    }

    /**
     * @param iterable<mixed> $translations
     * @return array<string, mixed>|null
     */
    public function resolve(iterable $translations, ?string $currentLocale = null): ?array
    {
        $translationMap = [];

        foreach ($translations as $translation) {
            $payload = $this->toArray($translation);
            $locale = $payload['locale'] ?? null;

            if (! is_string($locale) || $locale === '') {
                continue;
            }

            $translationMap[$locale] = $payload;
        }

        foreach ($this->preferredLocales($currentLocale) as $locale) {
            if (array_key_exists($locale, $translationMap)) {
                return $translationMap[$locale];
            }
        }

        return null;
    }

    /**
     * @param iterable<mixed> $translations
     */
    public function resolveField(iterable $translations, string $field, ?string $currentLocale = null): ?string
    {
        $resolved = $this->resolve($translations, $currentLocale);
        $value = $resolved[$field] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(mixed $translation): array
    {
        if (is_array($translation)) {
            return $translation;
        }

        if (is_object($translation) && method_exists($translation, 'toArray')) {
            /** @var array<string, mixed> $resolved */
            $resolved = $translation->toArray();

            return $resolved;
        }

        if (is_object($translation)) {
            /** @var array<string, mixed> $resolved */
            $resolved = get_object_vars($translation);

            return $resolved;
        }

        return [];
    }
}
