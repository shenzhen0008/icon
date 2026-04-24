<?php

namespace App\Modules\ClientEnv\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEnvDecisionSetting extends Model
{
    protected $fillable = [
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}

