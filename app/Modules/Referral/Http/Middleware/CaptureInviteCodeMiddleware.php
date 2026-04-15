<?php

namespace App\Modules\Referral\Http\Middleware;

use App\Modules\Referral\Support\InviteCodeCookieSigner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CaptureInviteCodeMiddleware
{
    public function __construct(private readonly InviteCodeCookieSigner $cookieSigner)
    {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inviteCode = $request->query('invite_code');

        if (is_string($inviteCode)
            && preg_match('/^[A-Za-z0-9]{6,32}$/', $inviteCode) === 1
            && $request->user() === null) {
            $inviteCode = strtoupper($inviteCode);

            $request->session()->put((string) config('referral.invite_code_session_key'), $inviteCode);

            Cookie::queue(
                (string) config('referral.invite_code_cookie_name'),
                $this->cookieSigner->sign($inviteCode),
                (int) config('referral.invite_code_cookie_minutes')
            );
        }

        return $next($request);
    }
}
