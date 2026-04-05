<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\Auth\LoginRequest;
use App\Modules\User\Services\AuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuthenticationService $authenticationService)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $authenticated = $this->authenticationService->attemptLogin(
            $request,
            (string) $request->validated('username'),
            (string) $request->validated('password'),
            $request->remember(),
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        return redirect('/me');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authenticationService->logout($request);

        return redirect('/');
    }
}
