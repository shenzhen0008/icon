<?php

namespace App\Modules\Help\Services;

use App\Modules\Help\Models\HelpItem;
use App\Modules\I18n\Services\TranslationFallbackResolver;

class LocalizedHelpFaqService
{
    public function __construct(private readonly TranslationFallbackResolver $resolver)
    {
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public function listFaqs(?string $currentLocale = null): array
    {
        $helpItems = HelpItem::query()
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return $helpItems
            ->map(function (HelpItem $helpItem) use ($currentLocale): ?array {
                $resolved = $this->resolver->resolve($helpItem->translations, $currentLocale);
                $question = $resolved['question'] ?? null;
                $answer = $resolved['answer'] ?? null;

                if (! is_string($question) || ! is_string($answer)) {
                    return null;
                }

                return [
                    'question' => $question,
                    'answer' => $answer,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
