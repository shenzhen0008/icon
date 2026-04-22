<?php

namespace App\Modules\ClientEnv\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ClientEnv\Services\ClientEnvDetectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectClientEnvController extends Controller
{
    public function __construct(private readonly ClientEnvDetectorService $detectorService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!(bool) config('client_env.enabled', true)) {
            abort(403);
        }

        $data = $this->detectorService->detect($request);

        if ((bool) config('client_env.expose_raw_user_agent', false)) {
            $data['raw_user_agent'] = (string) $request->userAgent();
        }

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
