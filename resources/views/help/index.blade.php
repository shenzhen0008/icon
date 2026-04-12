<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>帮助中心 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-5xl px-4 pb-28 pt-6 md:pb-10 md:pt-8">
    <section class="rounded-3xl border border-theme bg-theme-card p-6 shadow-xl shadow-[rgb(var(--theme-primary))]/10">
      <p class="text-scale-body font-semibold text-[rgb(var(--theme-primary))]">帮助中心</p>
      <h1 class="mt-2 text-scale-display font-semibold text-theme">常见问题</h1>
      <p class="mt-3 max-w-2xl text-scale-body leading-6 text-theme-secondary">
        先把常用问题放在这里。点击问题展开答案，再点一次就会收起。
      </p>
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
