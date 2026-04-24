<?php

namespace Tests\Feature\ClientEnv;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientEnvDecisionSettingManagementPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'is_admin' => true,
        ]));
    }

    public function test_admin_can_open_client_env_decision_setting_page(): void
    {
        $this->get('/admin/client-env-decision-settings/1/edit')
            ->assertOk()
            ->assertSee('环境检测风控开关')
            ->assertSee('启用第二层判定（allow/deny）');
    }

    public function test_admin_can_disable_and_enable_second_layer_decision(): void
    {
        Livewire::test(\App\Filament\Resources\ClientEnvDecisionSettings\Pages\EditClientEnvDecisionSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'is_enabled' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('client_env_decision_settings', [
            'id' => 1,
            'is_enabled' => false,
        ]);

        Livewire::test(\App\Filament\Resources\ClientEnvDecisionSettings\Pages\EditClientEnvDecisionSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'is_enabled' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('client_env_decision_settings', [
            'id' => 1,
            'is_enabled' => true,
        ]);
    }
}

