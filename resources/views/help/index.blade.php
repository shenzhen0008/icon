<!doctype html>
<html lang="{{ __('pages/help-center.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/help-center.meta_title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="rounded-3xl border border-theme bg-theme-card p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-scale-body font-semibold text-[rgb(var(--theme-primary))]">{{ __('pages/help-center.title') }}</p>
          <h1 class="mt-2 text-scale-display font-semibold text-theme">{{ __('pages/help-center.faq_title') }}</h1>
          <p class="mt-3 max-w-2xl text-scale-body leading-6 text-theme-secondary">
            {{ __('pages/help-center.intro') }}
          </p>
        </div>

        <a
          href="/stream-chat"
          class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-5 py-3 text-scale-body font-semibold text-[rgb(var(--theme-on-primary))] transition hover:opacity-90"
        >
          {{ __('pages/help-center.online_support') }}
        </a>
      </div>
    </section>

    <section class="mt-6 space-y-3">
      @foreach ($faqs as $faq)
        <details class="group overflow-hidden rounded-2xl border border-theme bg-theme-card">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left">
            <span class="text-scale-body font-semibold text-theme">{{ $faq['question'] }}</span>
            <span class="shrink-0 text-theme-secondary transition group-open:rotate-45">+</span>
          </summary>
          <div class="border-t border-theme bg-theme-secondary/20 px-5 py-4">
            <p class="text-scale-body leading-6 text-theme-secondary">{{ $faq['answer'] }}</p>
          </div>
        </details>
      @endforeach
    </section>
  </main>

  <x-nav.mobile />
</body>
</html>
