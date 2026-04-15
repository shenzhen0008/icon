<?php

namespace App\Modules\Referral\Services;

use App\Models\User;
use App\Modules\Referral\Support\InviteCodeCookieSigner;
use App\Modules\Referral\Support\InviteCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class BindReferrerOnRegisterService
{
    public function __construct(
        private readonly InviteCodeGenerator $inviteCodeGenerator,
        private readonly InviteCodeCookieSigner $cookieSigner,
    ) {
    }

    public function handle(User $user, Request $request): void
    {
        if ($user->invite_code === null || $user->invite_code === '') {
            $user->invite_code = $this->inviteCodeGenerator->generate((int) config('referral.invite_code_length'));
            $user->save();
        }

        if ($user->referrer_id !== null) {
            $this->clearInvite($request);

            return;
        }

        $inviteCode = $this->resolveInviteCode($request);

        if ($inviteCode !== null) {
            $referrer = User::query()
                ->where('invite_code', $inviteCode)
                ->first();

            if ($referrer !== null && $referrer->id !== $user->id) {
                $user->referrer_id = $referrer->id;
                $user->save();
            }
        }

        $this->clearInvite($request);
    }

    private function resolveInviteCode(Request $request): ?string
    {
        $submitted = $request->input('invite_code');
        if (is_string($submitted) && trim($submitted) !== '') {
            return strtoupper(trim($submitted));
        }

        $sessionValue = $request->session()->get((string) config('referral.invite_code_session_key'));
        if (is_string($sessionValue) && trim($sessionValue) !== '') {
            return strtoupper(trim($sessionValue));
        }

        $cookieName = (string) config('referral.invite_code_cookie_name');
        $cookieValue = $request->cookie($cookieName) ?? $this->rawCookie($request, $cookieName);
        $verified = $this->cookieSigner->verify(is_string($cookieValue) ? $cookieValue : null);

        return $verified === null ? null : strtoupper($verified);
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

    private function clearInvite(Request $request): void
    {
        $request->session()->forget((string) config('referral.invite_code_session_key'));
        Cookie::queue(Cookie::forget((string) config('referral.invite_code_cookie_name')));
    }
}
