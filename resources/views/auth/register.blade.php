<!DOCTYPE html>
<html lang="{{ __('pages/auth.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ __('pages/auth.register.meta_title', ['app_name' => config('app.name')]) }}</title>
    <x-meta.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto max-w-md px-6 pb-4 pt-8 md:pb-8">
        <h1 class="mb-2 text-scale-display font-semibold">{{ __('pages/auth.register.title') }}</h1>
        <p class="mb-6 text-scale-body text-theme-secondary">{{ __('pages/auth.register.temp_username', ['username' => session(config('user.temp_username_session_key'))]) }}</p>
        <p class="mb-6 text-scale-body text-theme-secondary">{{ __('pages/auth.register.pin_hint') }}</p>

        <form method="POST" action="/register" class="space-y-4 rounded-lg border border-theme bg-theme-secondary p-4">
            @csrf
            <input type="hidden" name="invite_code" value="{{ app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request()) }}">

            <div>
                <label class="mb-1 block text-scale-body" for="password">{{ __('pages/auth.register.pin_label') }}</label>
                <input id="password" type="password" name="password" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
                @error('password')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-scale-body" for="password_confirmation">{{ __('pages/auth.register.pin_confirm_label') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
            </div>

            <button class="w-full rounded bg-[rgb(var(--theme-primary))] px-4 py-2 font-medium text-theme-secondary">{{ __('pages/auth.register.submit') }}</button>
        </form>
    </main>

    <x-nav.mobile />
</body>
</html>
