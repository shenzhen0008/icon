<?php

namespace Tests\Feature\Savings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsYieldSettingManagementPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'is_admin' => true,
        ]));
    }

    public function test_admin_can_open_savings_yield_setting_page(): void
    {
        $this->get('/admin/savings-yield-settings/1/edit')
            ->assertOk()
            ->assertSee('储蓄收益率')
            ->assertSee('日收益率')
            ->assertSee('手动触发当日结算');
    }

    public function test_admin_can_save_valid_savings_yield_rate(): void
    {
        Livewire::test(\App\Filament\Resources\SavingsYieldSettings\Pages\EditSavingsYieldSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'daily_rate' => '0.0030',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('savings_yield_settings', [
            'id' => 1,
            'daily_rate' => '0.0030',
            'is_active' => true,
        ]);
    }

    public function test_rate_must_be_between_zero_and_one(): void
    {
        Livewire::test(\App\Filament\Resources\SavingsYieldSettings\Pages\EditSavingsYieldSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'daily_rate' => '1.0000',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['daily_rate']);

        Livewire::test(\App\Filament\Resources\SavingsYieldSettings\Pages\EditSavingsYieldSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'daily_rate' => '-0.0100',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['daily_rate']);
    }
}
