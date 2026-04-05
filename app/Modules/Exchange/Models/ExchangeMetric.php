<?php

namespace App\Modules\Exchange\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange_code',
        'exchange_name',
        'btc_value',
        'btc_liquidity',
        'eth_value',
        'eth_liquidity',
        'total_value',
        'profit_value',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'btc_value' => 'decimal:8',
            'btc_liquidity' => 'int',
            'eth_value' => 'decimal:8',
            'eth_liquidity' => 'int',
            'total_value' => 'decimal:8',
            'profit_value' => 'decimal:2',
            'sort' => 'int',
            'is_active' => 'bool',
        ];
    }
}
