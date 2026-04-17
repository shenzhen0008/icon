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
        'purchase_limit_count',
        'limit_min_usdt',
        'limit_max_usdt',
        'rate_min_percent',
        'rate_max_percent',
        'cycle_days',
        'product_icon_path',
        'symbol_icon_paths',
        'trade_mode',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'purchase_limit_count' => 'integer',
            'limit_min_usdt' => 'decimal:2',
            'limit_max_usdt' => 'decimal:2',
            'rate_min_percent' => 'decimal:2',
            'rate_max_percent' => 'decimal:2',
            'symbol_icon_paths' => 'array',
            'trade_mode' => 'string',
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

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }
}
