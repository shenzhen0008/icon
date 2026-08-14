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
        'display_btc_liquidity',
        'display_eth_liquidity',
        'liquidity_step_seconds',
        'liquidity_min_delta',
        'liquidity_max_delta',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'int',
            'is_active' => 'bool',
            'liquidity_step_seconds' => 'int',
            'liquidity_min_delta' => 'decimal:2',
            'liquidity_max_delta' => 'decimal:2',
        ];
    }
}
