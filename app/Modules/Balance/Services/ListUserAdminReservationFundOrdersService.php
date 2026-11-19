<?php

namespace App\Modules\Balance\Services;

use App\Models\User;
use App\Modules\Balance\Models\AdminReservationFundOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListUserAdminReservationFundOrdersService
{
    public function handle(User $user): Collection
    {
        return $this->query($user)
            ->latest()
            ->latest('id')
            ->get();
    }

    public function count(User $user): int
    {
        return $this->query($user)->count();
    }

    private function query(User $user): Builder
    {
        return AdminReservationFundOrder::query()
            ->where('user_id', $user->id)
            ->where('type', AdminReservationFundOrder::DEBIT)
            ->whereDoesntHave('releaseLedger');
    }
}
