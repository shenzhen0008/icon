<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as Middleware;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;

class AuthenticateAdminPanel extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        $canAccess = $user instanceof FilamentUser
            ? $user->canAccessPanel($panel)
            : (config('app.env') === 'local');

        if ($canAccess) {
            return;
        }

        // Non-admin user hitting admin panel: force re-auth on admin login page.
        $guard->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $this->unauthenticated($request, $guards);
    }
}

