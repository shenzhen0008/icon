@php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
@endphp

<header class="mx-auto hidden w-full max-w-4xl items-center justify-between px-6 py-6 md:flex">
    <a href="/?{{ $localeQuery }}" class="text-scale-ui font-semibold tracking-[0.2em] text-cyan-300">{{ config('app.name') }}</a>
    <nav class="text-scale-ui flex items-center gap-6 text-slate-300">
        <a href="/?{{ $localeQuery }}" class="transition hover:text-cyan-200">{{ __('pages/home.nav.home') }}</a>
        <a href="/products?{{ $localeQuery }}" class="transition hover:text-cyan-200">{{ __('pages/home.nav.products') }}</a>
        <a href="/help?{{ $localeQuery }}" class="transition hover:text-cyan-200">{{ __('pages/home.nav.help') }}</a>
        <a href="/me?{{ $localeQuery }}" class="transition hover:text-cyan-200">{{ __('pages/home.nav.me') }}</a>
    </nav>
</header>
