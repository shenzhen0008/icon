<?php

namespace App\Modules\Referral\Support;

use App\Models\User;

class InviteCodeGenerator
{
    private const POOL = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generate(int $length): string
    {
        $maxRetries = 10;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $code = $this->randomCode($length);

            if (! User::query()->where('invite_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to allocate unique invite code after retries.');
    }

    private function randomCode(int $length): string
    {
        $code = '';
        $maxIndex = strlen(self::POOL) - 1;

        for ($index = 0; $index < $length; $index++) {
            $code .= self::POOL[random_int(0, $maxIndex)];
        }

        return $code;
    }
}
