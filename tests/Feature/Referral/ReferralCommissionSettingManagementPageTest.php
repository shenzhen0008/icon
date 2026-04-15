<?php

namespace Tests\Feature\Referral;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReferralCommissionSettingManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_referral_commission_setting_page(): void
    {
        $this->get('/admin/referral-commission-settings/1/edit')
            ->assertOk()
            ->assertSee('推荐提成比例')
            ->assertSee('一级提成比例')
            ->assertSee('二级提成比例');
    }

    public function test_admin_can_save_valid_referral_commission_rates(): void
    {
        Livewire::test(\App\Filament\Resources\ReferralCommissionSettings\Pages\EditReferralCommissionSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'level_1_rate' => '0.0800',
                'level_2_rate' => '0.0300',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_commission_settings', [
            'id' => 1,
            'level_1_rate' => '0.0800',
            'level_2_rate' => '0.0300',
            'is_active' => true,
        ]);
    }

    public function test_level_two_rate_cannot_exceed_level_one_rate(): void
    {
        Livewire::test(\App\Filament\Resources\ReferralCommissionSettings\Pages\EditReferralCommissionSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'level_1_rate' => '0.0500',
                'level_2_rate' => '0.0600',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['level_2_rate']);
    }

    public function test_rates_must_be_between_zero_and_one(): void
    {
        Livewire::test(\App\Filament\Resources\ReferralCommissionSettings\Pages\EditReferralCommissionSetting::class, [
            'record' => 1,
        ])
            ->fillForm([
                'level_1_rate' => '1.0000',
                'level_2_rate' => '-0.0100',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['level_1_rate', 'level_2_rate']);
    }
}
