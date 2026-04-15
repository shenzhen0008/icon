<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class HomeDisplaySetting extends Model
{
    protected $fillable = [
        'summary_people_count',
        'summary_people_step_seconds',
        'summary_people_min_delta',
        'summary_people_max_delta',
        'summary_people_last_tick_at',
        'summary_total_profit',
        'summary_profit_step_seconds',
        'summary_profit_min_delta',
        'summary_profit_max_delta',
        'summary_profit_last_tick_at',
        'shared_exchange_profit_base_value',
        'shared_exchange_profit_step_seconds',
        'shared_exchange_profit_min_delta',
        'shared_exchange_profit_max_delta',
    ];

    protected function casts(): array
    {
        return [
            'summary_people_step_seconds' => 'int',
            'summary_people_min_delta' => 'decimal:2',
            'summary_people_max_delta' => 'decimal:2',
            'summary_people_last_tick_at' => 'datetime',
            'summary_profit_step_seconds' => 'int',
            'summary_profit_min_delta' => 'decimal:2',
            'summary_profit_max_delta' => 'decimal:2',
            'summary_profit_last_tick_at' => 'datetime',
            'shared_exchange_profit_base_value' => 'decimal:2',
            'shared_exchange_profit_step_seconds' => 'int',
            'shared_exchange_profit_min_delta' => 'decimal:2',
            'shared_exchange_profit_max_delta' => 'decimal:2',
        ];
    }
}
