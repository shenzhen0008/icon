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

    public function test_guest_home_summary_returns_null_popup_even_if_campaign_exists(): void
    {
        $target = User::factory()->create();
        $campaignId = $this->createCampaignForUsers([$target->id]);

        $this->get('/home-summary')
            ->assertOk()
            ->assertJsonPath('popup', null);

        $this->actingAs($target)
            ->get('/home-summary')
            ->assertOk()
            ->assertJsonPath('popup.campaign_id', $campaignId);
    }

    public function test_non_target_user_cannot_receive_popup(): void
    {
        $target = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createCampaignForUsers([$target->id]);

        $this->actingAs($otherUser)
            ->get('/home-summary')
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
            ->get('/home-summary')
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
            ->get('/home-summary')
            ->assertOk()
            ->assertJsonPath('popup.campaign_id', $campaignId);

        Carbon::setTestNow();
    }

    /**
     * @param array<int> $userIds
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
