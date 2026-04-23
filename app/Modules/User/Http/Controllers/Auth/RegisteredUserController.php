<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Referral\Services\BindReferrerOnRegisterService;
use App\Modules\User\Http\Requests\Auth\ActivateAccountRequest;
use App\Modules\User\Services\AccountActivationService;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly TemporaryAccountService $temporaryAccountService,
        private readonly AccountActivationService $accountActivationService,
        private readonly BindReferrerOnRegisterService $bindReferrerOnRegisterService,
    ) {
    }

    public function create(Request $request): View
    {
        $this->temporaryAccountService->ensureGuestTempUsername($request);

        return view('auth.register');
    }

    public function store(ActivateAccountRequest $request): RedirectResponse
    {
        $temporaryUsername = $this->temporaryAccountService->getFromSession($request);

        if ($temporaryUsername === null) {
            abort(403);
        }

        $user = $this->accountActivationService->createUserFromTemporaryUsername(
            $temporaryUsername,
            (string) $request->validated('password'),
        );

        $this->bindReferrerOnRegisterService->handle($user, $request);

        Auth::login($user, true);
        $request->session()->regenerate();
        $this->temporaryAccountService->clearFromSession($request);
        $request->session()->flash('show_mnemonic_setup_prompt', true);

        $redirectTo = (string) $request->input('redirect_to', '/me');
        if (! str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
            $redirectTo = '/me';
        }

        return redirect($redirectTo);
    }
}
