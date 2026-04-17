<?php

namespace App\Modules\BankRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankRecharge\Http\Requests\StoreBankRechargePaymentRequest;
use App\Modules\BankRecharge\Services\CreateBankRechargeRequestService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SubmitBankRechargeRequestController extends Controller
{
    public function __construct(private readonly CreateBankRechargeRequestService $service)
    {
    }

    public function __invoke(StoreBankRechargePaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        try {
            $receiptImage = $request->file('receipt_image');
            if (! $receiptImage instanceof UploadedFile) {
                throw ValidationException::withMessages([
                    'receipt_image' => '付款截图上传失败，请重试。',
                ]);
            }

            $this->service->handle(
                $user,
                (string) $request->validated('receiver_key'),
                (string) $request->validated('payment_amount'),
                $receiptImage,
                $request->validated('user_note'),
            );
        } catch (ValidationException $exception) {
            throw $exception->redirectTo('/recharge/bank?mode=receive');
        }

        return redirect('/recharge/bank?mode=receive')->with('success', __('pages/recharge.receive.success_submitted'));
    }
}
