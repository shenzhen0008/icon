<?php

namespace Tests\Feature\ClientEnv;

use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientEnvAccessReminderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_redirects_home_when_decision_is_disabled_by_admin_setting(): void
    {
        ClientEnvDecisionSetting::query()->create([
            'is_enabled' => false,
        ]);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get('/client-env/access-reminder')
            ->assertRedirect(url('/'));
    }

    public function test_page_redirects_home_when_user_agent_matches_wallet_keyword(): void
    {
        config()->set('client_env.decision.wallet_keywords', ['metamask']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 MetaMaskMobile/7.35.0',
        ])->get('/client-env/access-reminder')
            ->assertRedirect(url('/'));
    }

    public function test_page_renders_when_decision_enabled_and_user_agent_not_matched(): void
    {
        config()->set('client_env.decision.wallet_keywords', ['metamask']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/135.0.0.0 Safari/537.36',
        ])->get('/client-env/access-reminder')
            ->assertOk()
            ->assertViewIs('client-env.access-reminder');
    }
}
