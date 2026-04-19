@php
  $localeQuery = 'locale='.urlencode(app()->getLocale());
@endphp

<x-ui.metric-split-card :use-split-layout="false" wrapper-class="home-data-panel">
  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/me.guest_panel.title') }}</h2>
  <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/me.guest_panel.description') }}</p>

  <div class="mt-4 flex flex-wrap items-center gap-3">
    <button id="open-activate-modal" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold text-theme-on-primary">{{ __('pages/me.guest_panel.activate_button') }}</button>
    <a href="/login?{{ $localeQuery }}" class="text-scale-body text-[rgb(var(--theme-primary))] underline underline-offset-4">{{ __('pages/me.guest_panel.login_link') }}</a>
  </div>
</x-ui.metric-split-card>
