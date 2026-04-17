<?php

namespace App\Modules\Withdrawal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Withdrawal\Http\Requests\StoreWithdrawalRequest;
use App\Modules\Withdrawal\Services\SubmitWithdrawalRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $redirectTarget = $this->resolveRedirectTarget($request);
        $payload = $request->validated();
        $payload = $this->adaptBankCardWithdrawalPayload($payload);

        try {
            $this->submitWithdrawalRequestService->submit($user, $payload);
        } catch (ValidationException $exception) {
            throw $exception->redirectTo($redirectTarget);
        }

        return redirect($redirectTarget)->with('success', '提款申请已提交，请等待管理员审核。');
    }

    private function resolveRedirectTarget(Request $request): string
    {
        $previous = (string) url()->previous();
        if (str_starts_with($previous, url('/recharge/bank'))) {
            return '/recharge/bank?mode=send';
        }

        return '/recharge?mode=send';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function adaptBankCardWithdrawalPayload(array $payload): array
    {
        $network = (string) ($payload['network'] ?? '');
        if ($network !== 'BANK_CARD') {
            return $payload;
        }

        $payload['asset_code'] = 'BANK';
        $payload['destination_address'] = (string) ($payload['card_number'] ?? '');
        $payload['meta_json'] = [
            'bank_name' => (string) ($payload['bank_name'] ?? ''),
            'account_name' => (string) ($payload['account_name'] ?? ''),
            'card_number' => (string) ($payload['card_number'] ?? ''),
            'branch_name' => (string) ($payload['branch_name'] ?? ''),
            'reserved_phone' => (string) ($payload['reserved_phone'] ?? ''),
            'bank_note' => (string) ($payload['bank_note'] ?? ''),
            'submitted_from' => 'bank_recharge_page',
        ];

        return $payload;
    }
}
