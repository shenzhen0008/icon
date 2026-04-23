<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\Auth\MnemonicLoginRequest;
use App\Modules\User\Services\MnemonicAuthService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class MnemonicLoginController extends Controller
{
    public function __construct(private readonly MnemonicAuthService $mnemonicAuthService)
    {
    }

    public function __invoke(MnemonicLoginRequest $request): RedirectResponse
    {
        try {
            $authenticated = $this->mnemonicAuthService->attemptLogin(
                (string) $request->validated('mnemonic_phrase'),
                $request->remember(),
            );
        } catch (InvalidArgumentException) {
            return back()
                ->withInput($request->only('mnemonic_phrase'))
                ->withErrors(['mnemonic_phrase' => __('auth.failed')]);
        }

        if (! $authenticated) {
            return back()
                ->withInput($request->only('mnemonic_phrase'))
                ->withErrors(['mnemonic_phrase' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect('/me');
    }
}
