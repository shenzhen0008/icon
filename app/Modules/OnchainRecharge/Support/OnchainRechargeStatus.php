<?php

namespace App\Modules\OnchainRecharge\Support;

final class OnchainRechargeStatus
{
    public const CHANNEL_MANUAL_TRANSFER = 'manual_transfer';

    public const CHANNEL_ONCHAIN_WALLET = 'onchain_wallet';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_REJECTED = 'rejected';

    private function __construct()
    {
    }
}
