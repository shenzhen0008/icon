<?php

namespace App\Modules\OnchainRecharge\Support;

final class TxHashNormalizer
{
    public static function normalize(string $txHash): string
    {
        return strtolower(trim($txHash));
    }

    private function __construct()
    {
    }
}
