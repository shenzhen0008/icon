<?php

namespace Tests\Unit\I18n;

use App\Modules\I18n\Services\TranslationFallbackResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TranslationFallbackResolverTest extends TestCase
{
    public function test_preferred_locales_are_deduplicated_and_ordered(): void
    {
        config()->set('app.locale', 'en');
        config()->set('i18n.fallback_locale', 'zh-CN');

        $service = app(TranslationFallbackResolver::class);

        $this->assertSame(['en', 'zh-CN'], $service->preferredLocales());
    }

    #[DataProvider('resolveTranslationDataProvider')]
    public function test_it_resolves_translation_by_locale_priority(array $translations, string $currentLocale, ?string $expectedQuestion): void
    {
        config()->set('app.locale', $currentLocale);
        config()->set('i18n.fallback_locale', 'zh-CN');

        $service = app(TranslationFallbackResolver::class);

        $resolved = $service->resolve($translations);

        $this->assertSame($expectedQuestion, $resolved['question'] ?? null);
    }

    /**
     * @return array<string, array{0: array<int, array<string, string>>, 1: string, 2: string|null}>
     */
    public static function resolveTranslationDataProvider(): array
    {
        return [
            'hit-current-locale' => [
                [
                    ['locale' => 'zh-CN', 'question' => '中文问题'],
                    ['locale' => 'en', 'question' => 'English Question'],
                ],
                'en',
                'English Question',
            ],
            'fallback-to-default-locale' => [
                [
                    ['locale' => 'zh-CN', 'question' => '中文问题'],
                    ['locale' => 'ja', 'question' => '日本語の質問'],
                ],
                'en',
                '中文问题',
            ],
            'return-null-when-no-matching-locale' => [
                [
                    ['locale' => 'ja', 'question' => '日本語の質問'],
                ],
                'en',
                null,
            ],
        ];
    }
}
