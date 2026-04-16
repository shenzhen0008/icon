<?php

namespace App\Modules\PopupPush\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PopupPush\Services\PopupReceiptService;
use Illuminate\Http\JsonResponse;

class MarkPopupConfirmedController extends Controller
{
    public function __construct(private readonly PopupReceiptService $popupReceiptService)
    {
    }

    public function __invoke(int $campaign): JsonResponse
    {
        $userId = (int) auth()->id();
        $this->popupReceiptService->markConfirmed($campaign, $userId);

        return response()->json(['ok' => true]);
    }
}
