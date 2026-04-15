<?php

namespace App\Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Home\Services\HomeSummaryService;
use Illuminate\Http\JsonResponse;

class HomeSummaryFeedController extends Controller
{
    public function __construct(private readonly HomeSummaryService $homeSummaryService)
    {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json($this->homeSummaryService->resolve());
    }
}
