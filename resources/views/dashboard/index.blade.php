<!DOCTYPE html>
<html lang="{{ __('pages/dashboard.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ __('pages/dashboard.meta_title', ['app_name' => config('app.name')]) }}</title>
    <x-meta.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto max-w-2xl px-6 pb-28 pt-8 md:pb-10">
        <h1 class="mb-2 text-scale-display font-semibold">{{ __('pages/dashboard.title') }}</h1>
        <p class="text-theme-secondary">{{ __('pages/dashboard.current_user', ['username' => $user?->username]) }}</p>

        <div class="mt-6 flex gap-3">
            <a href="/sensitive" class="rounded bg-theme-secondary px-4 py-2">{{ __('pages/dashboard.sensitive_page') }}</a>
            <form method="POST" action="/logout">
                @csrf
                <button class="rounded bg-[rgb(var(--theme-rose))] px-4 py-2 text-theme-secondary">{{ __('pages/dashboard.logout') }}</button>
            </form>
        </div>
    </main>

    <x-nav.mobile />
</body>
</html>
