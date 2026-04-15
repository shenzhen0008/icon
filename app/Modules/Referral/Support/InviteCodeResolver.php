<?php

namespace App\Modules\Referral\Support;

use Illuminate\Http\Request;

class InviteCodeResolver
{
    public function __construct(private readonly InviteCodeCookieSigner $cookieSigner)
    {
    }

    public function currentForForm(Request $request): string
    {
        $old = old('invite_code');
        if (is_string($old) && trim($old) !== '') {
            return trim($old);
        }

        $sessionValue = $request->session()->get((string) config('referral.invite_code_session_key'));
        if (is_string($sessionValue) && trim($sessionValue) !== '') {
            return trim($sessionValue);
        }

        $cookieName = (string) config('referral.invite_code_cookie_name');
        $cookieValue = $request->cookie($cookieName) ?? $this->rawCookie($request, $cookieName);
        $verified = $this->cookieSigner->verify(is_string($cookieValue) ? $cookieValue : null);

        return $verified ?? '';
    }

    private function rawCookie(Request $request, string $name): ?string
    {
        $cookieHeader = $request->headers->get('cookie');
        if (! is_string($cookieHeader)) {
            return null;
        }

        foreach (explode(';', $cookieHeader) as $cookie) {
            [$cookieName, $cookieValue] = array_pad(explode('=', trim($cookie), 2), 2, null);

            if ($cookieName === $name && is_string($cookieValue)) {
                return $cookieValue;
            }
        }

        return null;
    }
}
