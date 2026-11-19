<?php

namespace App\Modules\Balance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdminReservationFundOrder extends Model
{
    public const DEBIT = 'reservation_fund_debit';

    public const RELEASE = 'reservation_fund_release';

    public const REFERENCE_TYPE = 'admin_reservation_fund_order';

    protected $fillable = ['user_id', 'type', 'name', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function releaseLedger(): HasOne
    {
        return $this->hasOne(BalanceLedger::class, 'biz_ref_id')
            ->where('biz_ref_type', self::REFERENCE_TYPE)
            ->where('type', self::RELEASE);
    }

    public function signedAmount(): string
    {
        return ($this->type === self::DEBIT ? '-' : '+').$this->amount;
    }
}
