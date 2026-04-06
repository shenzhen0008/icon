<?php

namespace App\Modules\Product\Models;

use App\Modules\Position\Models\Position;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'unit_price',
        'purchase_limit',
        'limit_min_usdt',
        'limit_max_usdt',
        'rate_min_percent',
        'rate_max_percent',
        'cycle_days',
        'product_icon_path',
        'symbol_icon_paths',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'limit_min_usdt' => 'decimal:2',
            'limit_max_usdt' => 'decimal:2',
            'rate_min_percent' => 'decimal:2',
            'rate_max_percent' => 'decimal:2',
            'symbol_icon_paths' => 'array',
            'is_active' => 'bool',
        ];
    }

    public function dailyReturns(): HasMany
    {
        return $this->hasMany(ProductDailyReturn::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
