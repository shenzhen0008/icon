<?php

namespace App\Modules\User\Services;

use App\Modules\User\Support\UsernameGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemporaryAccountService
{
    public function __construct(private readonly UsernameGenerator $usernameGenerator)
    {
    }

    public function ensureGuestTempUsername(Request $request): ?string
    {
        if (Auth::guard('web')->check()) {
            return null;
        }

        $sessionKey = (string) config('user.temp_username_session_key');
        $existing = $request->session()->get($sessionKey);

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $generated = $this->usernameGenerator->generate((int) config('user.username_length'));
        $request->session()->put($sessionKey, $generated);

        return $generated;
    }

    public function getFromSession(Request $request): ?string
    {
        $value = $request->session()->get((string) config('user.temp_username_session_key'));

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function clearFromSession(Request $request): void
    {
        $request->session()->forget((string) config('user.temp_username_session_key'));
    }
}
