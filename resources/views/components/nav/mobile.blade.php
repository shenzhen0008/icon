<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-slate-950/95 backdrop-blur md:hidden">
  <div class="mx-auto grid max-w-6xl grid-cols-3">
    <a href="/" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('/') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>首页</span>
    </a>
    <a href="/products" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('products') || request()->is('products/*') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>产品</span>
    </a>
    <a href="/me" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('me') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>我的</span>
    </a>
  </div>
</nav>
