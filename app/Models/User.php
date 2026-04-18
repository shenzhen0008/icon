<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;

#[Fillable(['username', 'name', 'remark', 'email', 'email_verified_at', 'password', 'balance', 'invite_code', 'referrer_id', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'referrer_id' => 'integer',
            'is_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return Gate::forUser($this)->allows('access-admin');
    }
}
