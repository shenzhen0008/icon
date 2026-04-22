<?php

namespace Tests\Feature\ClientEnv;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollectClientEnvTest extends TestCase
{
    public function test_collect_endpoint_supports_get_request_for_browser_manual_probe(): void
    {
        Storage::fake('local');

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
            'X-Request-Id' => 'req_get_probe_001',
        ])->get('/dev/client-env/collect');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('saved', true)
            ->assertJsonPath('entry.request_id', 'req_get_probe_001');

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        Storage::disk('local')->assertExists($path);

        $records = $this->readProbeRecords($path);
        $payload = $records[0];

        $this->assertSame('desktop', $payload['server_detect']['device_type']);
    }

    public function test_collect_endpoint_writes_jsonl_probe_log(): void
    {
        Storage::fake('local');

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1',
            'X-Request-Id' => 'req_test_001',
        ])->postJson('/dev/client-env/collect', [
            'client' => [
                'browser_name' => 'Safari',
                'browser_version' => '18.0',
                'platform' => 'iOS',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('saved', true)
            ->assertJsonPath('entry.request_id', 'req_test_001');

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        Storage::disk('local')->assertExists($path);

        $records = $this->readProbeRecords($path);
        $payload = $records[0];

        $this->assertSame('req_test_001', $payload['request_id']);
        $this->assertSame('Safari', $payload['client_reported']['browser_name']);
        $this->assertSame('mobile', $payload['server_detect']['device_type']);
        $this->assertSame('iOS', $payload['server_detect']['os']['name']);
    }

    public function test_collect_endpoint_deduplicates_same_browser_for_same_user_key(): void
    {
        Storage::fake('local');

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        ];

        $this->withHeaders($headers + ['X-Request-Id' => 'req_dup_1'])
            ->get('/dev/client-env/collect')
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('duplicate', false);

        $this->withHeaders($headers + ['X-Request-Id' => 'req_dup_2'])
            ->get('/dev/client-env/collect')
            ->assertOk()
            ->assertJsonPath('saved', false)
            ->assertJsonPath('duplicate', true);

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        $records = $this->readProbeRecords($path);

        $this->assertCount(1, $records);
        $this->assertSame('req_dup_1', $records[0]['request_id']);
    }

    public function test_collect_endpoint_creates_new_record_for_different_user_agent(): void
    {
        Storage::fake('local');

        $this->withHeaders([
            'User-Agent' => 'CustomProbeA/1.0',
            'X-Request-Id' => 'req_ua_a',
        ])->get('/dev/client-env/collect')
            ->assertOk()
            ->assertJsonPath('saved', true);

        $this->withHeaders([
            'User-Agent' => 'CustomProbeB/1.0',
            'X-Request-Id' => 'req_ua_b',
        ])->get('/dev/client-env/collect')
            ->assertOk()
            ->assertJsonPath('saved', true);

        $path = (string) config('client_env.log_path', 'client-env/probe-log.jsonl');
        $records = $this->readProbeRecords($path);

        $this->assertCount(2, $records);
        $this->assertSame('req_ua_a', $records[0]['request_id']);
        $this->assertSame('req_ua_b', $records[1]['request_id']);
    }

    public function test_collect_endpoint_rejects_invalid_payload(): void
    {
        $this->postJson('/dev/client-env/collect', [
            'client' => 'invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['client']);
    }

    public function test_collect_endpoint_returns_forbidden_when_feature_is_disabled(): void
    {
        config()->set('client_env.enabled', false);

        $this->postJson('/dev/client-env/collect', [
            'client' => [
                'browser_name' => 'Chrome',
            ],
        ])->assertForbidden();
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
