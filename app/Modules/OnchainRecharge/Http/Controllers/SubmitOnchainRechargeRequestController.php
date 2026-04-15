<?php

namespace App\Modules\OnchainRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OnchainRecharge\Http\Requests\StoreOnchainRechargeRequest;
use App\Modules\OnchainRecharge\Services\CreateOnchainRechargeRequestService;
use Illuminate\Http\RedirectResponse;

class SubmitOnchainRechargeRequestController extends Controller
{
    public function __invoke(StoreOnchainRechargeRequest $request, CreateOnchainRechargeRequestService $service): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $service->handle($user, $request->validated());

        return redirect('/recharge/onchain')->with('success', '链上充值申请已提交，请等待客服核账。');
    }
}
