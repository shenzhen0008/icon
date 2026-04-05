<header class="sticky top-0 z-30 border-b border-white/10 bg-slate-950/90 backdrop-blur">
  <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
    <a href="/" class="text-sm font-semibold tracking-[0.2em] text-cyan-300">ICON MARKET</a>
    <nav class="hidden items-center gap-6 text-sm md:flex">
      <a href="/" class="{{ request()->is('/') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">首页</a>
      <a href="/products" class="{{ request()->is('products') || request()->is('products/*') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">产品</a>
      <a href="/me" class="{{ request()->is('me') ? 'text-cyan-200' : 'text-slate-300 hover:text-cyan-200' }}">我的</a>
    </nav>
  </div>
</header>
