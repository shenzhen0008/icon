<?php

namespace App\Modules\ClientEnv\Services;

use Illuminate\Support\Facades\Storage;

class ClientEnvProbeLogService
{
    /**
     * @param array<string, mixed> $entry
     */
    public function appendUnique(array $entry): bool
    {
        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        $existing = $this->readAll($path);
        $uniqueKey = (string) ($entry['unique_key'] ?? '');

        if ($uniqueKey !== '' && $this->containsUniqueKey($existing, $uniqueKey)) {
            return false;
        }

        $existing[] = $entry;

        $blocks = array_map(
            static fn (array $item): string => json_encode(
                $item,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            ),
            $existing
        );

        Storage::disk('local')->put($path, implode("\n\n", $blocks)."\n");

        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readAll(string $path): array
    {
        if (!Storage::disk('local')->exists($path)) {
            return [];
        }

        $content = trim((string) Storage::disk('local')->get($path));
        if ($content === '') {
            return [];
        }

        $blocks = preg_split('/\n\s*\n/', $content) ?: [];
        $entries = [];

        foreach ($blocks as $block) {
            $decoded = json_decode($block, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        return $entries;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private function containsUniqueKey(array $entries, string $uniqueKey): bool
    {
        foreach ($entries as $item) {
            if ((string) ($item['unique_key'] ?? '') === $uniqueKey) {
                return true;
            }
        }

        return false;
    }
}
