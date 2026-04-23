<?php

namespace App\Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

class MnemonicAuthService
{
    public function attemptLogin(string $mnemonicPhrase, bool $remember): bool
    {
        $normalizedPhrase = $this->normalizeAndValidatePhrase($mnemonicPhrase);
        $lookup = $this->buildLookup($normalizedPhrase);

        $user = User::query()->where('mnemonic_lookup', $lookup)->first();

        if ($user === null) {
            return false;
        }

        Auth::guard('web')->login($user, $remember);

        return true;
    }

    public function generateAndAssignToUser(User $user): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $words = $this->pickWordsFromConfiguredWordlist();
            $phrase = implode(' ', $words);
            $lookup = $this->buildLookup($phrase);

            $isUsedByAnotherUser = User::query()
                ->where('mnemonic_lookup', $lookup)
                ->where('id', '!=', $user->getKey())
                ->exists();

            if ($isUsedByAnotherUser) {
                continue;
            }

            $user->forceFill([
                'mnemonic_lookup' => $lookup,
            ])->save();

            return $phrase;
        }

        throw new RuntimeException('Failed to generate a unique mnemonic phrase.');
    }

    public function normalizeAndValidatePhrase(string $mnemonicPhrase): string
    {
        $normalizedPhrase = strtolower(trim(preg_replace('/\s+/', ' ', $mnemonicPhrase) ?? ''));

        if ($normalizedPhrase === '') {
            throw new InvalidArgumentException('Mnemonic phrase is empty.');
        }

        $words = explode(' ', $normalizedPhrase);
        $expectedCount = $this->configuredPhraseWordsCount();

        if (count($words) !== $expectedCount) {
            throw new InvalidArgumentException('Mnemonic phrase word count is invalid.');
        }

        $wordlistLookup = array_fill_keys($this->configuredWordlist(), true);

        foreach ($words as $word) {
            if (! isset($wordlistLookup[$word])) {
                throw new InvalidArgumentException('Mnemonic phrase contains unsupported words.');
            }
        }

        return implode(' ', $words);
    }

    public function buildLookup(string $normalizedPhrase): string
    {
        return hash('sha256', $normalizedPhrase);
    }

    /**
     * @return array<int, string>
     */
    private function pickWordsFromConfiguredWordlist(): array
    {
        $wordlist = $this->configuredWordlist();
        $count = $this->configuredPhraseWordsCount();

        if (count($wordlist) < $count) {
            throw new RuntimeException('Configured mnemonic wordlist is smaller than phrase words count.');
        }

        $keys = (array) Arr::random(array_keys($wordlist), $count);
        $words = array_map(static fn (int|string $key): string => $wordlist[(int) $key], $keys);
        shuffle($words);

        return $words;
    }

    /**
     * @return array<int, string>
     */
    private function configuredWordlist(): array
    {
        $wordlist = array_values(array_unique(array_map(
            static fn (mixed $word): string => strtolower(trim((string) $word)),
            (array) config('mnemonic.wordlist', []),
        )));

        return array_values(array_filter($wordlist, static fn (string $word): bool => $word !== ''));
    }

    private function configuredPhraseWordsCount(): int
    {
        $count = (int) config('mnemonic.phrase_words_count', 10);

        return max(1, $count);
    }
}
