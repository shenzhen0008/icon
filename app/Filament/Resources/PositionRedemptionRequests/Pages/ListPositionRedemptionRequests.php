<?php

namespace App\Filament\Resources\PositionRedemptionRequests\Pages;

use App\Filament\Resources\PositionRedemptionRequests\PositionRedemptionRequestResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPositionRedemptionRequests extends ListRecords
{
    protected static string $resource = PositionRedemptionRequestResource::class;

    public function getSubheading(): string | Htmlable | null
    {
        return '提示：赎回功能已暂时下线，当前页面仅用于查看历史申请记录，请勿继续审批处理。';
    }
}
