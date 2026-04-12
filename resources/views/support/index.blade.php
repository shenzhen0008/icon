<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>客服支持 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-6xl px-6 pb-[calc(var(--mobile-nav-height,4.25rem)+1.5rem+env(safe-area-inset-bottom))] pt-8 md:pb-10">
    <div class="mb-5">
      <p class="text-scale-micro uppercase tracking-[0.24em] text-[rgb(var(--theme-primary))]">Support</p>
      <h1 class="mt-2 text-scale-display font-semibold">客服中心</h1>
      <p class="mt-1 text-scale-body text-theme-secondary">在站内页面直接联系在线客服。</p>
    </div>

    @if ($tawkEnabled && filled($embedUrl))
      <section class="rounded-2xl border border-[rgb(var(--theme-primary))]/20 bg-theme-card p-6 text-scale-body text-theme-secondary shadow-xl shadow-[rgb(var(--theme-primary))]/10">
        客服已接入。点击页面右下角聊天入口即可开始会话。
      </section>
    @else
      <section class="rounded-2xl border border-dashed border-theme bg-theme-card p-8 text-scale-body text-theme-secondary">
        客服系统暂未配置完成，请稍后再试。
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
