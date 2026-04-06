<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>客服支持 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <x-nav.top />

  <main class="mx-auto w-full max-w-6xl px-6 pb-28 pt-8 md:pb-10">
    <div class="mb-5">
      <p class="text-xs uppercase tracking-[0.24em] text-cyan-300">Support</p>
      <h1 class="mt-2 text-2xl font-semibold">客服中心</h1>
      <p class="mt-1 text-sm text-slate-400">在站内页面直接联系在线客服。</p>
    </div>

    @if ($tawkEnabled && filled($embedUrl))
      <section class="rounded-2xl border border-cyan-400/20 bg-slate-900/80 p-6 text-sm text-slate-300 shadow-xl shadow-cyan-500/10">
        客服已接入。点击页面右下角聊天入口即可开始会话。
      </section>
    @else
      <section class="rounded-2xl border border-dashed border-white/20 bg-slate-900/60 p-8 text-sm text-slate-300">
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
