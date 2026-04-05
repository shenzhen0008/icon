<?php

namespace App\Modules\User\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
    public function attemptLogin(Request $request, string $username, string $password, bool $remember): bool
    {
        $authenticated = Auth::attempt([
            'username' => $username,
            'password' => $password,
        ], $remember);

        if (! $authenticated) {
            return false;
        }

        $request->session()->regenerate();

        return true;
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
