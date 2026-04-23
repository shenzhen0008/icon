<!doctype html>
<html lang="{{ __('pages/me.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/me.account.mnemonic_title') }} | {{ config('app.name') }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
      <h1 class="text-scale-display font-semibold text-theme">{{ __('pages/me.account.mnemonic_title') }}</h1>
      <div class="mt-3 rounded-xl border border-[rgb(var(--theme-rose))]/35 bg-[rgb(var(--theme-rose))]/12 p-3">
        <p class="text-scale-body font-semibold text-theme">{{ __('pages/me.account.mnemonic_warning_title') }}</p>
        <p class="mt-1 text-scale-body text-theme-secondary">{{ __('pages/me.account.mnemonic_warning_body') }}</p>
      </div>

      <form method="POST" action="/me/mnemonic/regenerate" class="mt-4">
        @csrf
        <button class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 font-semibold text-theme-on-primary">{{ __('pages/me.account.mnemonic_regenerate') }}</button>
      </form>

      @if (session('generated_mnemonic_phrase'))
        <div class="mt-4 rounded-lg border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/10 p-3">
          <p class="text-scale-micro text-theme-secondary">{{ __('pages/me.account.mnemonic_generated_hint') }}</p>
          @php
            $mnemonicWords = preg_split('/\s+/', trim((string) session('generated_mnemonic_phrase'))) ?: [];
          @endphp
          <div class="mt-2 grid grid-cols-5 gap-2">
            @foreach ($mnemonicWords as $word)
              <span class="inline-flex items-center justify-center rounded-lg border border-[rgb(var(--theme-primary))]/30 bg-[rgb(var(--theme-primary))]/20 px-2 py-2 text-center text-scale-body font-semibold text-theme">
                {{ $word }}
              </span>
            @endforeach
          </div>
        </div>
      @endif

    </x-ui.metric-split-card>
  </main>

  <x-nav.mobile />
</body>
</html>
