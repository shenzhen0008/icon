<?php

namespace App\Modules\OnchainRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportWalletClientEventController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'stage' => ['required', 'string', 'max:64'],
            'provider' => ['nullable', 'string', 'max:64'],
            'code' => ['nullable', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:500'],
            'details' => ['nullable', 'array'],
            'path' => ['nullable', 'string', 'max:255'],
            'chain_id' => ['nullable', 'string', 'max:32'],
        ]);

        Log::warning('onchain_wallet_client_event', [
            'event' => $payload,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json(['ok' => true]);
    }
}

