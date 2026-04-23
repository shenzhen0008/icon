<?php

namespace App\Modules\Referral\Services;

use App\Models\User;
use App\Modules\Referral\Support\InviteCodeGenerator;

class GetReferralDashboardService
{
    public function __construct(
        private readonly InviteCodeGenerator $inviteCodeGenerator,
        private readonly GetReferralCommissionSettingService $getSettingService,
    ) {
    }

    /**
     * @return array{
     *     invite_code:string,
     *     invite_url:string,
     *     level_1_rate:string,
     *     level_2_rate:string,
     *     level_one_count:int,
     *     level_two_count:int,
     *     level_one_users:array<int, array{username:string, created_at:string}>,
     *     level_two_users:array<int, array{username:string, created_at:string, parent_username:string}>
     * }
     */
    public function handle(User $user): array
    {
        $setting = $this->getSettingService->handle();

        if ($user->invite_code === null || $user->invite_code === '') {
            $user->invite_code = $this->inviteCodeGenerator->generate((int) config('referral.invite_code_length'));
            $user->save();
        }

        $levelOneUsers = User::query()
            ->where('referrer_id', $user->id)
            ->orderByDesc('id')
            ->get(['id', 'username', 'created_at'])
            ->map(fn (User $levelOneUser): array => [
                'username' => $levelOneUser->username,
                'created_at' => $levelOneUser->created_at?->format('Y-m-d H:i') ?? '--',
            ])
            ->all();

        $levelOneIds = User::query()
            ->where('referrer_id', $user->id)
            ->pluck('id');

        $levelTwoUsers = User::query()
            ->from('users as children')
            ->join('users as parents', 'children.referrer_id', '=', 'parents.id')
            ->whereIn('children.referrer_id', $levelOneIds)
            ->orderByDesc('children.id')
            ->get([
                'children.username',
                'children.created_at',
                'parents.username as parent_username',
            ])
            ->map(fn (object $levelTwoUser): array => [
                'username' => (string) $levelTwoUser->username,
                'created_at' => $levelTwoUser->created_at === null
                    ? '--'
                    : \Illuminate\Support\Carbon::parse($levelTwoUser->created_at)->format('Y-m-d H:i'),
                'parent_username' => (string) $levelTwoUser->parent_username,
            ])
            ->all();

        return [
            'invite_code' => $user->invite_code,
            'invite_url' => url('/').'?invite_code='.$user->invite_code,
            'level_1_rate' => $this->formatRateAsPercent($setting?->level_1_rate, '5%'),
            'level_2_rate' => $this->formatRateAsPercent($setting?->level_2_rate, '2%'),
            'level_one_count' => count($levelOneUsers),
            'level_two_count' => count($levelTwoUsers),
            'level_one_users' => $levelOneUsers,
            'level_two_users' => $levelTwoUsers,
        ];
    }

    private function formatRateAsPercent(null|string|float $rate, string $fallback): string
    {
        if ($rate === null || $rate === '') {
            return $fallback;
        }

        $percentage = number_format((float) $rate * 100, 4, '.', '');
        $percentage = rtrim(rtrim($percentage, '0'), '.');

        return $percentage.'%';
    }
}
