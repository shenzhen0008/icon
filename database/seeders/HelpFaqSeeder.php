<?php

namespace Database\Seeders;

use App\Modules\Help\Models\HelpItem;
use Illuminate\Database\Seeder;
use Throwable;

class HelpFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = $this->loadFaqs();

        if (! is_array($faqs) || $faqs === []) {
            return;
        }

        foreach (array_values($faqs) as $index => $faq) {
            if (! is_array($faq)) {
                continue;
            }

            $sort = (int) ($faq['sort'] ?? (($index + 1) * 10));
            $isActive = (bool) ($faq['is_active'] ?? true);
            $translations = is_array($faq['translations'] ?? null) ? $faq['translations'] : [];

            $helpItem = HelpItem::query()->updateOrCreate(
                ['sort' => $sort],
                [
                    'is_active' => $isActive,
                ]
            );

            foreach ($translations as $locale => $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                $question = $translation['question'] ?? null;
                $answer = $translation['answer'] ?? null;

                if (! is_string($question) || ! is_string($answer) || $question === '' || $answer === '') {
                    continue;
                }

                $helpItem->translations()->updateOrCreate(
                    ['locale' => (string) $locale],
                    [
                        'question' => $question,
                        'answer' => $answer,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFaqs(): array
    {
        $file = database_path('seeders/data/help_faq.json');

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
