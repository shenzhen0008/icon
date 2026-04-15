<?php

namespace App\Modules\OnchainRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OnchainRecharge\Http\Requests\StoreOnchainRechargeRequest;
use App\Modules\OnchainRecharge\Services\CreateOnchainRechargeRequestService;
use Illuminate\Http\JsonResponse;

class AutoSubmitOnchainRechargeRequestController extends Controller
{
    public function __invoke(StoreOnchainRechargeRequest $request, CreateOnchainRechargeRequestService $service): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $rechargeRequest = $service->handle($user, $request->validated());

        return response()->json([
            'message' => '链上充值申请已自动提交，请等待客服核账。',
            'request_id' => $rechargeRequest->id,
            'status' => (string) $rechargeRequest->status,
        ]);
    }
}

