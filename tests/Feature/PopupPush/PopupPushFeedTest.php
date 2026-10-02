<?php

namespace Tests\Feature\PopupPush;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PopupPushFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_popup_requires_authentication_and_returns_targeted_activity(): void
    {
        $target = User::factory()->create();
        $campaignId = $this->createCampaignForUsers([$target->id]);

        $this->get('/popup/pending')->assertRedirect('/login');
        $this->getJson('/popup/pending')->assertUnauthorized();

        $this->actingAs($target)
            ->getJson('/popup/pending')
            ->assertOk()
            ->assertJsonPath('popup.campaign_id', $campaignId);
    }

    public function test_non_target_user_cannot_receive_popup(): void
    {
        $target = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createCampaignForUsers([$target->id]);

        $this->actingAs($otherUser)
            ->getJson('/popup/pending?user_id='.$target->id)
            ->assertOk()
            ->assertJsonPath('popup', null);
    }

    public function test_confirmed_popup_is_no_longer_returned(): void
    {
        $target = User::factory()->create();
        $campaignId = $this->createCampaignForUsers([$target->id]);

        $this->actingAs($target)
            ->postJson("/popup/{$campaignId}/shown")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($target)
            ->postJson("/popup/{$campaignId}/confirm")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($target)
            ->getJson('/popup/pending')
            ->assertOk()
            ->assertJsonPath('popup', null);

        $receipt = DB::table('popup_receipts')
            ->where('campaign_id', $campaignId)
            ->where('user_id', $target->id)
            ->first();

        $this->assertNotNull($receipt);
        $this->assertNotNull($receipt->shown_at);
        $this->assertNotNull($receipt->confirmed_at);
    }

    public function test_popup_is_available_on_time_window_boundary(): void
    {
        Carbon::setTestNow('2026-04-16 10:00:00');

        $target = User::factory()->create();
        $campaignId = $this->createCampaignForUsers(
            [$target->id],
            startsAt: '2026-04-16 10:00:00',
            endsAt: '2026-04-16 10:00:00',
        );

        $this->actingAs($target)
            ->getJson('/popup/pending')
            ->assertOk()
            ->assertJsonPath('popup.campaign_id', $campaignId);

        Carbon::setTestNow();
    }

    public function test_pending_response_is_private_and_not_cacheable(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/popup/pending')->assertOk();

        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }

    public function test_only_latest_activity_is_returned_until_confirmed(): void
    {
        $target = User::factory()->create();
        $older = $this->createCampaignForUsers([$target->id]);
        $latest = $this->createCampaignForUsers([$target->id]);
        DB::table('popup_campaigns')->whereIn('id', [$older, $latest])->update(['created_at' => now()]);

        $this->actingAs($target)->getJson('/popup/pending')->assertJsonPath('popup.campaign_id', $latest);
        $this->postJson("/popup/{$latest}/shown")->assertOk();
        $this->getJson('/popup/pending')->assertJsonPath('popup.campaign_id', $latest);
        $this->postJson("/popup/{$latest}/confirm")->assertOk();
        $this->getJson('/popup/pending')->assertJsonPath('popup.campaign_id', $older);

        DB::table('popup_campaigns')->where('id', $older)->update(['created_at' => now()->addMinute()]);
        DB::table('popup_receipts')->where('campaign_id', $latest)->delete();
        $this->getJson('/popup/pending')->assertJsonPath('popup.campaign_id', $older);
    }

    public function test_inactive_future_and_expired_activities_are_not_returned(): void
    {
        $target = User::factory()->create();
        $inactive = $this->createCampaignForUsers([$target->id]);
        DB::table('popup_campaigns')->where('id', $inactive)->update(['status' => 'draft']);
        $this->createCampaignForUsers([$target->id], startsAt: now()->addDay()->toDateTimeString());
        $this->createCampaignForUsers([$target->id], endsAt: now()->subDay()->toDateTimeString());

        $this->actingAs($target)->getJson('/popup/pending')->assertOk()->assertJsonPath('popup', null);
    }

    public function test_receipts_reject_non_targets_invalid_ids_and_anonymous_users(): void
    {
        $target = User::factory()->create();
        $campaign = $this->createCampaignForUsers([$target->id]);

        foreach (['shown', 'confirm'] as $action) {
            $this->postJson("/popup/{$campaign}/{$action}")->assertUnauthorized();
        }

        $this->actingAs(User::factory()->create());
        foreach (['shown', 'confirm'] as $action) {
            $this->postJson("/popup/{$campaign}/{$action}")->assertUnprocessable()->assertJsonValidationErrors('campaign_id');
            $this->postJson("/popup/999999999/{$action}")->assertUnprocessable()->assertJsonValidationErrors('campaign_id');
        }

        $this->assertDatabaseCount('popup_receipts', 0);
    }

    public function test_receipts_are_idempotent_and_stats_do_not_include_popup(): void
    {
        $target = User::factory()->create();
        $campaign = $this->createCampaignForUsers([$target->id]);
        $this->actingAs($target);
        foreach (['shown', 'confirm', 'shown', 'confirm'] as $action) {
            $this->postJson("/popup/{$campaign}/{$action}")->assertOk()->assertJsonPath('ok', true);
        }
        $this->assertDatabaseCount('popup_receipts', 1);
        $this->getJson('/home-summary')->assertOk()->assertJsonMissingPath('popup');
        $this->postJson("/popup/{$campaign}/dismiss")->assertNotFound();
    }

    public function test_common_pages_mount_popup_once_outside_main(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/', '/me', '/products', '/recharge', '/me/orders'] as $path) {
            $response = $this->get($path)->assertOk();
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, 'id="pending-popup-modal"'), $path);
            $this->assertLessThan(strpos($html, '<main'), strpos($html, 'id="pending-popup-modal"'), $path);
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    private function createCampaignForUsers(array $userIds, ?string $startsAt = null, ?string $endsAt = null): int
    {
        $now = now();

        $campaignId = (int) DB::table('popup_campaigns')->insertGetId([
            'content' => '今晚 23:00-23:30 短时维护',
            'level' => 'warning',
            'requires_ack' => true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'sent',
            'created_by' => (int) User::factory()->create()->id,
            'sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($userIds as $userId) {
            DB::table('popup_campaign_user')->insert([
                'campaign_id' => $campaignId,
                'user_id' => (int) $userId,
                'delivery_status' => 'sent',
                'pushed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $campaignId;
    }
}
