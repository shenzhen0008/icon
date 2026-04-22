<?php

namespace Tests\Feature\ClientEnv;

use Tests\TestCase;

class DetectClientEnvTest extends TestCase
{
    public function test_detect_endpoint_returns_structured_payload(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Mobile Safari/537.36',
        ])->getJson('/dev/client-env/detect');

        $response->assertOk()
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'device_type',
                    'is_mobile',
                    'is_tablet',
                    'is_desktop',
                    'is_webview',
                    'browser' => ['name', 'version'],
                    'os' => ['name', 'version'],
                    'source',
                ],
            ])
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.device_type', 'mobile')
            ->assertJsonPath('data.browser.name', 'Chrome')
            ->assertJsonPath('data.os.name', 'Android');
    }

    public function test_detect_endpoint_degrades_to_unknown_without_user_agent(): void
    {
        $this->withHeaders([
            'User-Agent' => '',
        ])->getJson('/dev/client-env/detect')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.device_type', 'unknown')
            ->assertJsonPath('data.browser.name', 'unknown')
            ->assertJsonPath('data.os.name', 'unknown');
    }

    public function test_detect_endpoint_returns_forbidden_when_feature_is_disabled(): void
    {
        config()->set('client_env.enabled', false);

        $this->getJson('/dev/client-env/detect')->assertForbidden();
    }
}
