<?php

namespace App\Modules\Balance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargePaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_account',
        'asset_code',
        'payment_amount',
        'currency',
        'network',
        'receipt_address',
        'receipt_image_path',
        'status',
        'user_note',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'payment_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
