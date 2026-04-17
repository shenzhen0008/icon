<?php

namespace Tests\Feature\Referral;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralDashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_my_page_from_referral_dashboard(): void
    {
        $this->get('/referral')
            ->assertRedirect('/me');
    }

    public function test_authenticated_user_can_view_referral_dashboard_and_get_invite_code(): void
    {
        $user = User::factory()->create([
            'invite_code' => null,
        ]);

        $response = $this->actingAs($user)->get('/referral');

        $user->refresh();

        $response->assertOk()
            ->assertSee('bg-theme text-theme', false)
            ->assertSee('bg-[rgb(var(--theme-primary))]/20 blur-3xl', false)
            ->assertSee('bg-theme-card', false)
            ->assertSee('border-theme', false)
            ->assertSee('bg-[rgb(var(--theme-primary))]', false)
            ->assertSee('text-theme-on-primary', false)
            ->assertDontSee('bg-[#eef3ff]', false)
            ->assertSee('邀请分享')
            ->assertSee('奖励说明')
            ->assertSee('aria-label="查看奖励说明"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="reward-help-panel"', false)
            ->assertSee('class="absolute right-0 top-full', false)
            ->assertSee('w-[min(22rem,calc(100vw-2rem))]', false)
            ->assertSee('transition-all duration-200 ease-out', false)
            ->assertSee('opacity-0', false)
            ->assertSee('translate-y-1', false)
            ->assertSee('邀请好友有奖励吗？')
            ->assertSee('您可以通过个人专属链接邀请好友加入 AI 智能合约')
            ->assertSee('用户 A 可获得用户 C 参与 AI 智能合约收益的 3%')
            ->assertSee('所有奖励均由 AI 智能合约平台提供，不会影响任何用户自身的收益')
            ->assertSee('一级奖励 Direct')
            ->assertSee('5%')
            ->assertSee('二级奖励 Indirect')
            ->assertSee('2%')
            ->assertSee('一级邀请')
            ->assertSee('二级邀请')
            ->assertSee('邀请码')
            ->assertSee('邀请链接')
            ->assertSee($user->invite_code)
            ->assertSee('?invite_code='.$user->invite_code, false)
            ->assertSee('复制链接')
            ->assertSee('立即分享')
            ->assertDontSee('一级邀请用户')
            ->assertDontSee('二级邀请用户');

        $this->assertNotNull($user->invite_code);
    }

    public function test_referral_dashboard_renders_english_ui_copy_when_locale_is_en(): void
    {
        $user = User::factory()->create([
            'invite_code' => 'ENCASE01',
        ]);

        $this->actingAs($user)
            ->get('/referral?locale=en')
            ->assertOk()
            ->assertSee('Referral | Icon Market')
            ->assertSee('Referral Program')
            ->assertSee('Reward Details')
            ->assertSee('aria-label="View reward details"', false)
            ->assertSee('Is there a reward for inviting friends?')
            ->assertSee('Level 1 Reward (Direct)')
            ->assertSee('Level 2 Reward (Indirect)')
            ->assertSee('Invite Code')
            ->assertSee('Invite Link')
            ->assertSee('Copy Link')
            ->assertSee('Share Now');
    }

    public function test_referral_dashboard_shows_level_counts_only(): void
    {
        $user = User::factory()->create([
            'invite_code' => 'OWNER01',
        ]);

        $levelOne = User::factory()->create([
            'username' => 'LV100000000000000001',
            'invite_code' => 'LEVEL01',
            'referrer_id' => $user->id,
        ]);

        User::factory()->create([
            'username' => 'LV200000000000000001',
            'invite_code' => 'LEVEL02',
            'referrer_id' => $levelOne->id,
        ]);

        User::factory()->create([
            'username' => 'OTHER00000000000001',
            'invite_code' => 'OTHER01',
        ]);

        $this->actingAs($user)
            ->get('/referral')
            ->assertOk()
            ->assertSee('一级邀请')
            ->assertSee('二级邀请')
            ->assertSee('1')
            ->assertDontSee('OTHER00000000000001');
    }

    public function test_navigation_share_entry_links_to_referral_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="/referral"', false)
            ->assertDontSee('data-share-entry', false);
    }
}
