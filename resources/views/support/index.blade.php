<!doctype html>
<html lang="{{ __('pages/support.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/support.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-[calc(var(--mobile-nav-height,4.25rem)+1.5rem+env(safe-area-inset-bottom))] pt-8 md:px-6 md:pb-10">
    <div class="mb-5">
      <p class="text-scale-micro uppercase tracking-[0.24em] text-[rgb(var(--theme-primary))]">{{ __('pages/support.section_label') }}</p>
      <h1 class="mt-2 text-scale-display font-semibold">{{ __('pages/support.title') }}</h1>
      <p class="mt-1 text-scale-body text-theme-secondary">{{ __('pages/support.intro') }}</p>
    </div>

    @if ($tawkEnabled && filled($embedUrl))
      <section class="rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card p-6 text-scale-body text-theme-secondary shadow-xl shadow-[rgb(var(--theme-primary))]/10">
        {{ __('pages/support.ready_notice') }}
      </section>
    @else
      <section class="rounded-2xl border border-dashed border-theme bg-theme-card p-8 text-scale-body text-theme-secondary">
        {{ __('pages/support.unavailable_notice') }}
      </section>
    @endif
  </main>

  <x-nav.mobile />

  @if ($tawkEnabled && filled($embedUrl))
    <script type="text/javascript">
      window.Tawk_API = window.Tawk_API || {};
      window.Tawk_LoadStart = new Date();
      (() => {
        const s1 = document.createElement('script');
        const s0 = document.getElementsByTagName('script')[0];
        s1.async = true;
        s1.src = @json($embedUrl);
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
      })();
    </script>
  @endif
</body>
</html>
