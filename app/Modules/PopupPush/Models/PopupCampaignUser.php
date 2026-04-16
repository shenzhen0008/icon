<?php

namespace App\Modules\PopupPush\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupCampaignUser extends Model
{
    use HasFactory;

    protected $table = 'popup_campaign_user';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'delivery_status',
        'pushed_at',
    ];

    protected function casts(): array
    {
        return [
            'pushed_at' => 'datetime',
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
