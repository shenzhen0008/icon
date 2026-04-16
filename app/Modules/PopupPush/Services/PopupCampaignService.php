<?php

namespace App\Modules\PopupPush\Services;

use App\Modules\PopupPush\Models\PopupCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PopupCampaignService
{
    /**
     * @param array<int> $targetUserIds
     * @param array{content:string,requires_ack?:bool} $payload
     */
    public function createAndSend(array $targetUserIds, array $payload, int $createdBy): PopupCampaign
    {
        $userIds = array_values(array_unique(array_map('intval', $targetUserIds)));
        $userIds = array_values(array_filter($userIds, static fn (int $id): bool => $id > 0));

        if ($userIds === []) {
            throw ValidationException::withMessages([
                'user_ids' => '请至少选择一个目标用户。',
            ]);
        }

        return DB::transaction(function () use ($createdBy, $payload, $userIds): PopupCampaign {
            $now = now();

            $campaign = PopupCampaign::query()->create([
                'content' => trim((string) ($payload['content'] ?? '')),
                'level' => 'info',
                'requires_ack' => (bool) ($payload['requires_ack'] ?? false),
                'starts_at' => null,
                'ends_at' => null,
                'status' => 'sent',
                'created_by' => $createdBy,
                'sent_at' => $now,
            ]);

            $rows = array_map(static fn (int $userId): array => [
                'campaign_id' => $campaign->id,
                'user_id' => $userId,
                'delivery_status' => 'sent',
                'pushed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ], $userIds);

            DB::table('popup_campaign_user')->insert($rows);

            return $campaign;
        });
    }
}
