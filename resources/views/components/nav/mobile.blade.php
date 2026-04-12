<nav id="mobile-nav" class="fixed inset-x-0 bottom-0 z-30 border-t border-theme bg-theme-secondary/90 backdrop-blur md:hidden">
  <div class="mx-auto grid max-w-6xl grid-cols-5">
    <a href="/" class="text-scale-title flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('/') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>首页</span>
    </a>
    <a href="/products" class="text-scale-title flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('products') || request()->is('products/*') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>产品</span>
    </a>
    <a href="/help" class="text-scale-title flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('help') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>帮助</span>
    </a>
    <a href="/me" class="text-scale-title flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('me') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>我的</span>
    </a>
    <a href="/stream-chat" class="text-scale-title flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('stream-chat') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span class="relative inline-flex items-center gap-1">
        Stream
        <span data-stream-chat-unread-dot class="hidden h-2 w-2 rounded-full bg-[rgb(var(--theme-rose))]"></span>
      </span>
    </a>
  </div>
</nav>
