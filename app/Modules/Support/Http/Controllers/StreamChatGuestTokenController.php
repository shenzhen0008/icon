<?php

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Support\Services\StreamChatTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StreamChatGuestTokenController extends Controller
{
    public function __invoke(Request $request, StreamChatTokenService $service): JsonResponse
    {
        try {
            $payload = $service->issueGuestTokenPayload($request);

            return response()->json($payload);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }
    }
}
