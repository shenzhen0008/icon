<?php

namespace App\Modules\PopupPush\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'shown_at',
        'dismissed_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PopupCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
