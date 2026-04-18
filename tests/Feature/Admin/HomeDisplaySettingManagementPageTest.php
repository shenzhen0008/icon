<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeDisplaySettingManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_home_display_setting_page(): void
    {
        $this->get('/admin/home-display-settings/1/edit')
            ->assertOk()
            ->assertSee('首页展示数值')
            ->assertSee('Number of people')
            ->assertSee('总盘获利值')
            ->assertSee('人数跳动秒数')
            ->assertSee('获利跳动范围最小值')
            ->assertSee('统一平台获利基础值')
            ->assertSee('平台获利跳动秒数');
    }

    public function test_admin_can_save_home_display_values(): void
    {
        Livewire::test(\App\Filament\Resources\HomeDisplaySettings\Pages\EditHomeDisplaySetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'summary_people_count' => '66666',
                'summary_people_step_seconds' => '3',
                'summary_people_min_delta' => '-5',
                'summary_people_max_delta' => '10',
                'summary_total_profit' => '8888888',
                'summary_profit_step_seconds' => '3',
                'summary_profit_min_delta' => '-50',
                'summary_profit_max_delta' => '100',
                'shared_exchange_profit_base_value' => '2057.31',
                'shared_exchange_profit_step_seconds' => '3',
                'shared_exchange_profit_min_delta' => '-5',
                'shared_exchange_profit_max_delta' => '10',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('home_display_settings', [
            'id' => 1,
            'summary_people_count' => '66666',
            'summary_people_step_seconds' => 3,
            'summary_people_min_delta' => '-5.00',
            'summary_people_max_delta' => '10.00',
            'summary_total_profit' => '8888888.00',
            'summary_profit_step_seconds' => 3,
            'summary_profit_min_delta' => '-50.00',
            'summary_profit_max_delta' => '100.00',
            'shared_exchange_profit_base_value' => '2057.31',
            'shared_exchange_profit_step_seconds' => 3,
            'shared_exchange_profit_min_delta' => '-5.00',
            'shared_exchange_profit_max_delta' => '10.00',
        ]);
    }
}
