<?php

namespace App\Modules\BankRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BankRechargePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $mode = (string) $request->query('mode', 'receive');
        $mode = in_array($mode, ['receive', 'send'], true) ? $mode : 'receive';
        $receivers = $this->resolveBankReceivers();
        $receiverKeys = array_keys($receivers);
        $selectedReceiverKey = (string) old('receiver_key', (string) $request->query('receiver_key', $receiverKeys[0] ?? ''));
        if (! isset($receivers[$selectedReceiverKey])) {
            $selectedReceiverKey = $receiverKeys[0] ?? '';
        }

        return view('recharge.bank-index', [
            'mode' => $mode,
            'receivers' => $receivers,
            'selectedReceiverKey' => $selectedReceiverKey,
            'balance' => (float) $user->balance,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resolveBankReceivers(): array
    {
        $configured = (array) config('recharge.bank_receivers', []);
        $receivers = [];

        foreach ($configured as $key => $receiver) {
            if (! is_array($receiver)) {
                continue;
            }

            $enabled = (bool) ($receiver['enabled'] ?? false);
            $cardNumber = (string) ($receiver['card_number'] ?? '');
            $accountName = (string) ($receiver['account_name'] ?? '');
            if (! $enabled || $cardNumber === '' || $accountName === '') {
                continue;
            }

            $receivers[(string) $key] = [
                'key' => (string) $key,
                'code' => (string) ($receiver['code'] ?? strtoupper((string) $key)),
                'bank_name' => (string) ($receiver['bank_name'] ?? ''),
                'account_name' => $accountName,
                'card_number' => $cardNumber,
                'branch_name' => (string) ($receiver['branch_name'] ?? ''),
                'sort' => (int) ($receiver['sort'] ?? 0),
            ];
        }

        if ($receivers === []) {
            $legacy = (array) config('recharge.bank_receiver', []);
            $enabled = (bool) ($legacy['enabled'] ?? false);
            $cardNumber = (string) ($legacy['card_number'] ?? '');
            $accountName = (string) ($legacy['account_name'] ?? '');
            if ($enabled && $cardNumber !== '' && $accountName !== '') {
                $receivers['legacy'] = [
                    'key' => 'legacy',
                    'code' => 'BANK-LEGACY',
                    'bank_name' => (string) ($legacy['bank_name'] ?? ''),
                    'account_name' => $accountName,
                    'card_number' => $cardNumber,
                    'branch_name' => (string) ($legacy['branch_name'] ?? ''),
                    'sort' => 0,
                ];
            }
        }

        uasort($receivers, fn (array $a, array $b): int => ($a['sort'] <=> $b['sort']) ?: strcmp((string) $a['key'], (string) $b['key']));

        return $receivers;
    }
}
