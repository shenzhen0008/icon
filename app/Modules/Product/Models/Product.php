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
        'code',
        'unit_price',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
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
