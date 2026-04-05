<?php

namespace App\Modules\Settlement\Models;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'position_id',
        'settlement_date',
        'rate',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'settlement_date' => 'date',
            'rate' => 'decimal:4',
            'profit' => 'decimal:2',
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

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
