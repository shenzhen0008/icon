<?php

namespace App\Modules\PopupPush\Services;

use App\Models\User;
use App\Modules\PopupPush\Models\PopupCampaign;

class PopupFeedService
{
    /**
     * @return array{campaign_id:int,username:string,content:string,level:string,requires_ack:bool,ends_at:string|null}|null
     */
    public function resolveForUser(?int $userId): ?array
    {
        if ($userId === null) {
            return null;
        }

        $now = now();

        $campaign = PopupCampaign::query()
            ->select('popup_campaigns.*')
            ->join('popup_campaign_user as popup_targets', function ($join) use ($userId): void {
                $join->on('popup_targets.campaign_id', '=', 'popup_campaigns.id')
                    ->where('popup_targets.user_id', '=', $userId);
            })
            ->leftJoin('popup_receipts as popup_receipts', function ($join) use ($userId): void {
                $join->on('popup_receipts.campaign_id', '=', 'popup_campaigns.id')
                    ->where('popup_receipts.user_id', '=', $userId);
            })
            ->where('popup_campaigns.status', '=', 'sent')
            ->where(function ($query) use ($now): void {
                $query->whereNull('popup_campaigns.starts_at')
                    ->orWhere('popup_campaigns.starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('popup_campaigns.ends_at')
                    ->orWhere('popup_campaigns.ends_at', '>=', $now);
            })
            ->whereNull('popup_receipts.confirmed_at')
            ->orderByDesc('popup_campaigns.created_at')
            ->orderByDesc('popup_campaigns.id')
            ->first();

        if ($campaign === null) {
            return null;
        }

        $username = (string) (User::query()->whereKey($userId)->value('username') ?? '');

        return [
            'campaign_id' => (int) $campaign->id,
            'username' => $username,
            'content' => (string) $campaign->content,
            'level' => (string) $campaign->level,
            'requires_ack' => (bool) $campaign->requires_ack,
            'ends_at' => $campaign->ends_at?->format('Y-m-d H:i:s'),
        ];
    }
}
