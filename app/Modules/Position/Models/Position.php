<?php

namespace App\Modules\Position\Models;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Redemption\Models\PositionRedemptionRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'user_id',
        'product_id',
        'principal',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function redemptionRequests(): HasMany
    {
        return $this->hasMany(PositionRedemptionRequest::class);
    }
}
