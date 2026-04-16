<?php

namespace App\Modules\PopupPush\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopupCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'level',
        'requires_ack',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_ack' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PopupCampaignUser::class, 'campaign_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PopupReceipt::class, 'campaign_id');
    }
}
