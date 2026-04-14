<?php

namespace App\Modules\Reservation\Models;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'amount_usdt',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'approved_at',
        'converted_at',
        'converted_position_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_usdt' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function convertedPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'converted_position_id');
    }
}
