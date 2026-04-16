<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Withdrawal\Http\Requests\StoreWithdrawalRequest;
use App\Modules\Withdrawal\Services\SubmitWithdrawalRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SubmitWithdrawalRequestController extends Controller
{
    public function __construct(private readonly SubmitWithdrawalRequestService $submitWithdrawalRequestService)
    {
    }

    public function __invoke(StoreWithdrawalRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        try {
            $this->submitWithdrawalRequestService->submit($user, $request->validated());
        } catch (ValidationException $exception) {
            throw $exception->redirectTo('/recharge?mode=send');
        }

        return redirect('/recharge?mode=send')->with('success', '提款申请已提交，请等待管理员审核。');
    }
}
