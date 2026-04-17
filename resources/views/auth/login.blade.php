<!DOCTYPE html>
<html lang="{{ __('pages/login.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ __('pages/login.meta_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto max-w-md px-6 pb-28 pt-8 md:pb-10">
        <h1 class="mb-6 text-scale-display font-semibold">{{ __('pages/login.title') }}</h1>

        <form method="POST" action="/login" class="space-y-4 rounded-lg border border-theme bg-theme-secondary p-4">
            @csrf

            <div>
                <label class="mb-1 block text-scale-body" for="username">{{ __('pages/login.username_label') }}</label>
                <input id="username" name="username" value="{{ old('username') }}" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
                @error('username')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-scale-body" for="password">{{ __('pages/login.password_label') }}</label>
                <input id="password" type="password" name="password" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
                @error('password')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-scale-body">
                <input type="checkbox" name="remember" value="1" class="rounded border-theme bg-theme-secondary">
                {{ __('pages/login.remember_me') }}
            </label>

            <button class="w-full rounded bg-[rgb(var(--theme-primary))] px-4 py-2 font-medium text-theme-secondary">{{ __('pages/login.submit') }}</button>
        </form>
    </main>

    <x-nav.mobile />
</body>
</html>
