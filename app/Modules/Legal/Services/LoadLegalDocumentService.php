<?php

namespace App\Modules\Legal\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoadLegalDocumentService
{
    /**
     * @return array{html:\Illuminate\Support\HtmlString,resolved_locale:string,updated_at:string}
     */
    public function handle(string $type, string $locale): array
    {
        $normalizedType = $this->normalizeType($type);
        $path = $this->resolveDocumentPath($normalizedType, $locale);

        $markdown = file_get_contents($path);
        if (! is_string($markdown)) {
            throw new NotFoundHttpException('Legal document is unavailable.');
        }

        $resolvedLocale = $this->extractLocaleFromFilename($path);

        return [
            'html' => Str::markdown($markdown, [
                'html_input' => 'allow',
                'allow_unsafe_links' => false,
            ]),
            'resolved_locale' => $resolvedLocale,
            'updated_at' => Carbon::createFromTimestamp((int) filemtime($path))->toDateString(),
        ];
    }

    private function normalizeType(string $type): string
    {
        if (! in_array($type, ['privacy', 'terms'], true)) {
            throw new NotFoundHttpException('Legal document type is invalid.');
        }

        return $type;
    }

    private function resolveDocumentPath(string $type, string $locale): string
    {
        $fallbackLocale = (string) config('i18n.fallback_locale', 'en');

        $candidateLocales = array_values(array_unique(array_filter([
            $locale,
            $fallbackLocale,
            'en',
        ], static fn (mixed $item): bool => is_string($item) && $item !== '')));

        $baseDir = resource_path('content/legal');
        foreach ($candidateLocales as $candidateLocale) {
            $path = $baseDir.'/'.$type.'.'.$candidateLocale.'.md';
            if (is_file($path)) {
                return $path;
            }
        }

        throw new NotFoundHttpException('Legal document not found.');
    }

    private function extractLocaleFromFilename(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $parts = explode('.', $filename);

        return (string) end($parts);
    }
}
