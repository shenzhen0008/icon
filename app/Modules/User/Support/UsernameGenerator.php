<?php

namespace App\Modules\User\Support;

class UsernameGenerator
{
    private const POOL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function generate(int $length): string
    {
        $maxIndex = strlen(self::POOL) - 1;
        $username = '';

        for ($i = 0; $i < $length; $i++) {
            $username .= self::POOL[random_int(0, $maxIndex)];
        }

        return $username;
    }
}
