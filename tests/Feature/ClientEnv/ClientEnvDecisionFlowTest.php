<?php

namespace Tests\Feature\ClientEnv;

use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ClientEnvDecisionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shadow_mode_allows_request_and_writes_allow_audit_log(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'shadow');
        config()->set('client_env.decision.audit.enabled', true);
        config()->set('client_env.decision.audit.allow_sample_rate', 100);
        config()->set('client_env.decision.audit.allow_dedupe_ttl_seconds', 0);

        Route::middleware('web')->get('/__test/client-env-decision-shadow', function (Request $request) {
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'decision' => $decision['decision'] ?? null,
                'reason' => $decision['reason_code'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_shadow_1',
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 DeFiWallet/2.50.3',
        ])->getJson('/__test/client-env-decision-shadow')
            ->assertOk()
            ->assertJsonPath('decision', 'allow')
            ->assertJsonPath('reason', 'layer1_allow_wallet_keyword');

        $this->assertDatabaseHas('client_env_decision_logs', [
            'request_id' => 'req_decision_shadow_1',
            'decision' => 'allow',
            'reason_code' => 'layer1_allow_wallet_keyword',
            'route_key' => '__test/client-env-decision-shadow',
        ]);
    }

    public function test_enforce_mode_denies_request_and_writes_deny_audit_log(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.enforce_paths', ['__test/client-env-decision-enforce']);
        config()->set('client_env.decision.deny_score_threshold', 80);
        config()->set('client_env.decision.audit.enabled', true);
        config()->set('client_env.decision.audit.allow_sample_rate', 100);
        config()->set('client_env.decision.audit.allow_dedupe_ttl_seconds', 0);

        Route::middleware('web')->get('/__test/client-env-decision-enforce', fn () => response()->json(['ok' => true]));

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_enforce_1',
            'User-Agent' => '',
        ])->getJson('/__test/client-env-decision-enforce')
            ->assertForbidden();

        $this->assertDatabaseHas('client_env_decision_logs', [
            'request_id' => 'req_decision_enforce_1',
            'decision' => 'deny',
            'reason_code' => 'layer1_deny_wallet_keyword_not_matched',
            'route_key' => '__test/client-env-decision-enforce',
        ]);
    }

    public function test_invalid_client_env_header_is_treated_as_null_and_request_stays_allowed(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'shadow');
        config()->set('client_env.decision.audit.enabled', true);
        config()->set('client_env.decision.audit.allow_sample_rate', 100);
        config()->set('client_env.decision.audit.allow_dedupe_ttl_seconds', 0);

        Route::middleware('web')->get('/__test/client-env-decision-invalid-header', function (Request $request) {
            $probe = (array) $request->attributes->get('client_env_probe', []);
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'client_reported' => $probe['client_reported'] ?? null,
                'decision' => $decision['decision'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_invalid_header_1',
            'X-Client-Env' => '{invalid-json',
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 WebView MetaMaskMobile',
        ])->getJson('/__test/client-env-decision-invalid-header')
            ->assertOk()
            ->assertJsonPath('client_reported', null)
            ->assertJsonPath('decision', 'allow');
    }

    public function test_decision_can_be_disabled_by_admin_setting(): void
    {
        ClientEnvDecisionSetting::query()->create([
            'is_enabled' => false,
        ]);

        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.enforce_paths', ['__test/client-env-decision-setting-off']);

        Route::middleware('web')->get('/__test/client-env-decision-setting-off', function (Request $request) {
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'decision' => $decision['decision'] ?? null,
                'reason' => $decision['reason_code'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_setting_off_1',
            'User-Agent' => '',
        ])->getJson('/__test/client-env-decision-setting-off')
            ->assertOk()
            ->assertJsonPath('decision', 'allow')
            ->assertJsonPath('reason', 'decision_disabled');
    }

    public function test_decision_prefers_singleton_row_id_1_when_multiple_setting_rows_exist(): void
    {
        ClientEnvDecisionSetting::query()->create([
            'is_enabled' => true,
        ]);
        ClientEnvDecisionSetting::query()->create([
            'is_enabled' => true,
        ]);
        ClientEnvDecisionSetting::query()->updateOrCreate(
            ['id' => 1],
            ['is_enabled' => false],
        );

        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.enforce_paths', ['__test/client-env-decision-singleton']);

        Route::middleware('web')->get('/__test/client-env-decision-singleton', function (Request $request) {
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'decision' => $decision['decision'] ?? null,
                'reason' => $decision['reason_code'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_singleton_1',
            'User-Agent' => '',
        ])->getJson('/__test/client-env-decision-singleton')
            ->assertOk()
            ->assertJsonPath('decision', 'allow')
            ->assertJsonPath('reason', 'decision_disabled');
    }

    public function test_admin_path_is_excluded_from_second_layer_decision(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.excluded_paths', ['admin/*']);
        config()->set('client_env.decision.enforce_paths', ['*']);

        Route::middleware('web')->get('/admin/__test/client-env-decision-admin-excluded', function (Request $request) {
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'decision' => $decision['decision'] ?? null,
                'reason' => $decision['reason_code'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_admin_excluded_1',
            'User-Agent' => '',
        ])->getJson('/admin/__test/client-env-decision-admin-excluded')
            ->assertOk()
            ->assertJsonPath('decision', 'allow')
            ->assertJsonPath('reason', 'excluded_path');

        $this->assertDatabaseMissing('client_env_decision_logs', [
            'request_id' => 'req_decision_admin_excluded_1',
        ]);
    }

    public function test_enforce_mode_blocks_non_admin_paths_when_decision_is_deny(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.excluded_paths', ['admin/*']);
        config()->set('client_env.decision.enforce_paths', ['*']);

        Route::middleware('web')->get('/__test/client-env-decision-enforce-all', fn () => response()->json(['ok' => true]));

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_enforce_all_1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/135.0.0.0 Safari/537.36',
        ])->getJson('/__test/client-env-decision-enforce-all')
            ->assertForbidden();
    }

    public function test_livewire_path_is_excluded_from_second_layer_decision(): void
    {
        config()->set('client_env.middleware.enabled', true);
        config()->set('client_env.middleware.persist', false);
        config()->set('client_env.middleware.excluded_paths', []);
        config()->set('client_env.decision.enabled', true);
        config()->set('client_env.decision.mode', 'enforce');
        config()->set('client_env.decision.excluded_paths', ['admin/*', 'livewire/*']);
        config()->set('client_env.decision.enforce_paths', ['*']);

        Route::middleware('web')->post('/livewire/mock-update', function (Request $request) {
            $decision = (array) $request->attributes->get('client_env_decision', []);

            return response()->json([
                'decision' => $decision['decision'] ?? null,
                'reason' => $decision['reason_code'] ?? null,
            ]);
        });

        $this->withHeaders([
            'X-Request-Id' => 'req_decision_livewire_excluded_1',
            'User-Agent' => '',
        ])->postJson('/livewire/mock-update')
            ->assertOk()
            ->assertJsonPath('decision', 'allow')
            ->assertJsonPath('reason', 'excluded_path');
    }
}
