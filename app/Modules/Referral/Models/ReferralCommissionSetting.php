<?php

namespace App\Modules\Referral\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCommissionSetting extends Model
{
    protected $fillable = [
        'level_1_rate',
        'level_2_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_1_rate' => 'decimal:4',
            'level_2_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
