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
        'display_btc_volume',
        'display_btc_liquidity',
        'display_eth_volume',
        'display_eth_liquidity',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'int',
            'is_active' => 'bool',
        ];
    }
}
