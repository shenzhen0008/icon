<!DOCTYPE html>
<html lang="{{ __('pages/login.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ __('pages/login.meta_title', ['app_name' => config('app.name')]) }}</title>
    <x-meta.favicons />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto w-full max-w-[30rem] px-3 pb-[calc(var(--mobile-nav-height,4rem)+env(safe-area-inset-bottom)+0.5rem)] pt-5 md:px-0 md:pb-10 md:pt-10">
        <section class="pin-window-surface p-5 md:p-6">
            <div class="mb-5">
                <h1 class="text-scale-title font-semibold">{{ __('pages/login.title') }}</h1>
                <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/login.subtitle') }}</p>
            </div>

        @php
            $activeLoginMode = $errors->has('mnemonic_phrase') ? 'mnemonic' : 'password';
        @endphp

        <div class="mb-5 grid grid-cols-2 rounded-xl border border-theme bg-theme-secondary p-1" id="login-mode-switcher">
            <button
                type="button"
                data-login-mode-trigger="password"
                class="text-scale-ui rounded-lg px-3 py-2 font-semibold transition"
                aria-pressed="{{ $activeLoginMode === 'password' ? 'true' : 'false' }}"
            >
                {{ __('pages/login.password_mode_label') }}
            </button>
            <button
                type="button"
                data-login-mode-trigger="mnemonic"
                class="text-scale-ui rounded-lg px-3 py-2 font-semibold transition"
                aria-pressed="{{ $activeLoginMode === 'mnemonic' ? 'true' : 'false' }}"
            >
                {{ __('pages/login.mnemonic_mode_label') }}
            </button>
        </div>

        <section data-login-mode-panel="password" @class(['hidden' => $activeLoginMode !== 'password'])>
            <form method="POST" action="/login" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1 block text-scale-body" for="username">{{ __('pages/login.username_label') }}</label>
                    <input id="username" name="username" value="{{ old('username') }}" class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5" required>
                    @error('username')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-scale-body" for="password">{{ __('pages/login.password_label') }}</label>
                    <input id="password" type="password" name="password" class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5" required>
                    @error('password')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-scale-body">
                    <input type="checkbox" name="remember" value="1" class="rounded border-theme bg-theme-secondary">
                    {{ __('pages/login.remember_me') }}
                </label>

                <button class="text-scale-ui w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 font-semibold text-theme-on-primary">{{ __('pages/login.submit') }}</button>
            </form>
        </section>

        <section data-login-mode-panel="mnemonic" @class(['hidden' => $activeLoginMode !== 'mnemonic'])>
            <form method="POST" action="/login/mnemonic" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1 block text-scale-body" for="mnemonic_phrase">{{ __('pages/login.mnemonic_label') }}</label>
                    <textarea
                        id="mnemonic_phrase"
                        name="mnemonic_phrase"
                        rows="3"
                        class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5"
                        placeholder="{{ __('pages/login.mnemonic_placeholder') }}"
                        required
                    >{{ old('mnemonic_phrase') }}</textarea>
                    @error('mnemonic_phrase')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
                </div>
                <p class="-mt-1 text-scale-body text-theme-secondary">{{ __('pages/login.mnemonic_security_tip') }}</p>

                <label class="flex items-center gap-2 text-scale-body">
                    <input type="checkbox" name="remember" value="1" class="rounded border-theme bg-theme-secondary">
                    {{ __('pages/login.remember_me') }}
                </label>

                <button class="text-scale-ui w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 font-semibold text-theme-on-primary">{{ __('pages/login.mnemonic_submit') }}</button>
            </form>
        </section>
        </section>
    </main>

    <x-nav.mobile />

    <script>
        (() => {
            const triggerSelector = '[data-login-mode-trigger]';
            const panelSelector = '[data-login-mode-panel]';
            const activeTriggerClasses = ['bg-[rgb(var(--theme-primary))]', 'text-theme-on-primary'];
            const inactiveTriggerClasses = ['text-theme-secondary'];

            const setMode = (mode) => {
                document.querySelectorAll(triggerSelector).forEach((button) => {
                    const isActive = button.dataset.loginModeTrigger === mode;
                    activeTriggerClasses.forEach((className) => {
                        button.classList.toggle(className, isActive);
                    });
                    inactiveTriggerClasses.forEach((className) => {
                        button.classList.toggle(className, !isActive);
                    });
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                document.querySelectorAll(panelSelector).forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.loginModePanel !== mode);
                });
            };

            document.querySelectorAll(triggerSelector).forEach((button) => {
                button.addEventListener('click', () => setMode(button.dataset.loginModeTrigger));
            });

            setMode(@json($activeLoginMode));
        })();
    </script>
</body>
</html>
