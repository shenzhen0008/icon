<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-slate-950/95 backdrop-blur md:hidden">
  <div class="mx-auto grid max-w-6xl grid-cols-7">
    <a href="/" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('/') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>首页</span>
    </a>
    <a href="/admin" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('admin') || request()->is('admin/*') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>后台</span>
    </a>
    <a href="/products" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('products') || request()->is('products/*') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>产品</span>
    </a>
    <a href="/recharge" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('recharge') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>充值</span>
    </a>
    <a href="/me" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('me') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>我的</span>
    </a>
    <a href="/support" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('support') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span>客服</span>
    </a>
    <a href="/stream-chat" class="flex flex-col items-center gap-1 px-2 py-3 text-xs {{ request()->is('stream-chat') ? 'text-cyan-300' : 'text-slate-300' }}">
      <span class="relative inline-flex items-center gap-1">
        Stream
        <span data-stream-chat-unread-dot class="hidden h-2 w-2 rounded-full bg-rose-400"></span>
      </span>
    </a>
  </div>
</nav>
