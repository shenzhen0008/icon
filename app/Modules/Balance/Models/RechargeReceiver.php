<?php

namespace App\Modules\Balance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargeReceiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'asset_name',
        'network',
        'address',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
            'sort' => 'int',
        ];
    }
}
