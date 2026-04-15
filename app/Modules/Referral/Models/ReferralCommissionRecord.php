<?php

namespace App\Modules\Referral\Models;

use App\Models\User;
use App\Modules\Settlement\Models\DailySettlement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommissionRecord extends Model
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'settlement_id',
        'level',
        'referrer_id',
        'referred_user_id',
        'base_profit',
        'commission_rate',
        'commission_amount',
        'status',
        'granted_at',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'base_profit' => 'decimal:2',
            'commission_rate' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'granted_at' => 'datetime',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(DailySettlement::class, 'settlement_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
