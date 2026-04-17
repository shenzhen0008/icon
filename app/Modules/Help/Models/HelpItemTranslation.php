<?php

namespace App\Modules\Help\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpItemTranslation extends Model
{
    protected $fillable = [
        'help_item_id',
        'locale',
        'question',
        'answer',
    ];

    public function helpItem(): BelongsTo
    {
        return $this->belongsTo(HelpItem::class);
    }

    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * @param array<int, string> $locales
     */
    public function scopeForLocales(Builder $query, array $locales): Builder
    {
        return $query->whereIn('locale', $locales);
    }
}
