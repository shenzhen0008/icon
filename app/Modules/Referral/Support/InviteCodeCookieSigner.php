<?php

namespace App\Modules\Referral\Support;

class InviteCodeCookieSigner
{
    public function sign(string $inviteCode): string
    {
        $code = trim($inviteCode);

        return $code.'|'.$this->signature($code);
    }

    public function verify(?string $value): ?string
    {
        $value = is_string($value) ? urldecode($value) : $value;

        if (! is_string($value) || ! str_contains($value, '|')) {
            return null;
        }

        [$code, $signature] = explode('|', $value, 2);
        $code = trim($code);

        if ($code === '' || $signature === '') {
            return null;
        }

        if (! hash_equals($this->signature($code), $signature)) {
            return null;
        }

        return $code;
    }

    private function signature(string $inviteCode): string
    {
        return hash_hmac('sha256', $inviteCode, (string) config('app.key'));
    }
}
