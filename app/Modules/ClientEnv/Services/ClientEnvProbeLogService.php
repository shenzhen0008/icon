<?php

namespace App\Modules\ClientEnv\Services;

use Illuminate\Support\Facades\Storage;

class ClientEnvProbeLogService
{
    /**
     * @param array<string, mixed> $entry
     */
    public function append(array $entry): void
    {
        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');

        Storage::disk('local')->append(
            $path,
            json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );
    }
}
