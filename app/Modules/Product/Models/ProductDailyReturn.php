<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDailyReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'return_date',
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'rate' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
