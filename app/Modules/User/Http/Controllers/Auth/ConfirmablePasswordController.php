<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    public function store(ConfirmPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! Hash::check((string) $request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->passwordConfirmed();

        return redirect()->intended('/me');
    }
}
