<?php

namespace App\Modules\PopupPush\Services;

use App\Modules\PopupPush\Models\PopupCampaignUser;
use App\Modules\PopupPush\Models\PopupReceipt;
use Illuminate\Validation\ValidationException;

class PopupReceiptService
{
    public function markShown(int $campaignId, int $userId): void
    {
        $receipt = $this->ensureReceipt($campaignId, $userId);

        if ($receipt->shown_at !== null) {
            return;
        }

        $receipt->shown_at = now();
        $receipt->save();
    }

    public function markDismissed(int $campaignId, int $userId): void
    {
        $receipt = $this->ensureReceipt($campaignId, $userId);

        if ($receipt->shown_at === null) {
            $receipt->shown_at = now();
        }

        if ($receipt->dismissed_at === null) {
            $receipt->dismissed_at = now();
        }

        $receipt->save();
    }

    public function markConfirmed(int $campaignId, int $userId): void
    {
        $receipt = $this->ensureReceipt($campaignId, $userId);

        if ($receipt->shown_at === null) {
            $receipt->shown_at = now();
        }

        if ($receipt->confirmed_at === null) {
            $receipt->confirmed_at = now();
        }

        $receipt->save();
    }

    private function ensureReceipt(int $campaignId, int $userId): PopupReceipt
    {
        $isTargeted = PopupCampaignUser::query()
            ->where('campaign_id', $campaignId)
            ->where('user_id', $userId)
            ->exists();

        if (! $isTargeted) {
            throw ValidationException::withMessages([
                'campaign_id' => '该弹窗不属于当前用户。',
            ]);
        }

        return PopupReceipt::query()->firstOrCreate([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
        ]);
    }
}
