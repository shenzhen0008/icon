<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Position\Models\Position;

class PositionPolicy
{
    public function view(User $user, Position $position): bool
    {
        return (int) $user->id === (int) $position->user_id;
    }
}
