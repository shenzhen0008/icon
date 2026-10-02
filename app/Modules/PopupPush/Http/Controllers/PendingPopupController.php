<?php

namespace App\Modules\PopupPush\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PopupPush\Services\PopupFeedService;
use Illuminate\Http\JsonResponse;

class PendingPopupController extends Controller
{
    public function __construct(private readonly PopupFeedService $popupFeedService) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'popup' => $this->popupFeedService->resolveForUser(auth('web')->id()),
        ])->header('Cache-Control', 'private, no-store');
    }
}
