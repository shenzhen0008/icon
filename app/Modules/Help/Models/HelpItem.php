<?php

namespace App\Modules\Help\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpItem extends Model
{
    protected $fillable = [
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'bool',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HelpItemTranslation::class);
    }
}
