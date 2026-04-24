<?php

namespace App\Modules\Savings\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsYieldSetting extends Model
{
    protected $fillable = [
        'daily_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
