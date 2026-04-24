<?php

namespace Tests\Feature\ClientEnv;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientEnvProbeMiddlewareTest extends TestCase
{
    public function test_middleware_attaches_probe_context_and_persists_entry(): void
    {
        Storage::fake('local');
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', true);
        config()->set('client_env.middleware.excluded_paths', []);

        Route::middleware('web')->get('/__test/client-env-probe', function (Request $request) {
            $probe = $request->attributes->get('client_env_probe');

            return response()->json([
                'has_probe' => is_array($probe),
                'request_id' => $probe['request_id'] ?? null,
                'device_type' => $probe['server_detect']['device_type'] ?? null,
                'browser_name' => $probe['server_detect']['browser']['name'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_mid_001',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Mobile Safari/537.36',
        ])->getJson('/__test/client-env-probe')
            ->assertOk()
            ->assertJsonPath('has_probe', true)
            ->assertJsonPath('request_id', 'req_mid_001')
            ->assertJsonPath('device_type', 'mobile')
            ->assertJsonPath('browser_name', 'Chrome');

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        Storage::disk('local')->assertExists($path);
        $records = $this->readProbeRecords($path);

        $this->assertCount(1, $records);
        $this->assertSame('req_mid_001', $records[0]['request_id']);
    }

    public function test_middleware_skips_excluded_paths(): void
    {
        Storage::fake('local');
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', true);
        config()->set('client_env.middleware.excluded_paths', ['__test/client-env-excluded*']);

        Route::middleware('web')->get('/__test/client-env-excluded', function (Request $request) {
            return response()->json([
                'has_probe' => is_array($request->attributes->get('client_env_probe')),
            ]);
        });

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
        ])->getJson('/__test/client-env-excluded')
            ->assertOk()
            ->assertJsonPath('has_probe', false);

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        Storage::disk('local')->assertMissing($path);
    }

    public function test_middleware_can_be_disabled_by_config(): void
    {
        Storage::fake('local');
        config()->set('client_env.middleware.enabled', false);
        config()->set('client_env.middleware.persist', true);
        config()->set('client_env.middleware.excluded_paths', []);

        Route::middleware('web')->get('/__test/client-env-disabled', function (Request $request) {
            return response()->json([
                'has_probe' => is_array($request->attributes->get('client_env_probe')),
            ]);
        });

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->getJson('/__test/client-env-disabled')
            ->assertOk()
            ->assertJsonPath('has_probe', false);

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        Storage::disk('local')->assertMissing($path);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readProbeRecords(string $path): array
    {
        $content = trim((string) Storage::disk('local')->get($path));
        $blocks = preg_split('/\n\s*\n/', $content) ?: [];
        $records = [];

        foreach ($blocks as $block) {
            $records[] = json_decode($block, true, 512, JSON_THROW_ON_ERROR);
        }

        return $records;
    }
}

