@php
  $localeQuery = 'locale='.urlencode(app()->getLocale());
@endphp

<footer class="mx-auto w-full max-w-4xl px-4 pb-[calc(var(--mobile-nav-height,4rem)+env(safe-area-inset-bottom)+0.5rem)] pt-3 text-center text-scale-micro text-theme-secondary md:pb-3">
  <p>
    {{ __('pages/legal.footer_prefix', ['year' => '2024', 'site_name' => (string) config('app.name')]) }}
    ·
    <a href="/privacy?{{ $localeQuery }}" class="text-theme hover:text-[rgb(var(--theme-primary))]">{{ __('pages/legal.privacy_title') }}</a>
    ·
    <a href="/terms?{{ $localeQuery }}" class="text-theme hover:text-[rgb(var(--theme-primary))]">{{ __('pages/legal.terms_title') }}</a>
  </p>
</footer>
