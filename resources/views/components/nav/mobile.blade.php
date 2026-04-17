<nav id="mobile-nav" class="fixed inset-x-0 bottom-0 z-30 border-t border-theme bg-theme-secondary/90 backdrop-blur md:hidden">
  <div class="mx-auto grid max-w-4xl grid-cols-5">
    <a href="/" class="text-scale-ui flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('/') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>{{ __('pages/home.nav.home') }}</span>
    </a>
    <a href="/products" class="text-scale-ui flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('products') || request()->is('products/*') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>{{ __('pages/home.nav.products') }}</span>
    </a>
    <a href="/help" class="text-scale-ui flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('help') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>{{ __('pages/home.nav.help') }}</span>
    </a>
    <a href="/referral" class="text-scale-ui flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('referral') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>{{ __('pages/home.nav.share') }}</span>
    </a>
    <a href="/me" class="text-scale-ui flex min-h-[4rem] flex-col items-center justify-center gap-[0.2rem] px-[clamp(0.25rem,1.8vw,0.55rem)] py-[clamp(0.55rem,2.5vw,0.85rem)] font-semibold {{ request()->is('me') ? 'text-[rgb(var(--theme-primary))]' : 'text-theme-secondary hover:text-[rgb(var(--theme-primary))]' }}">
      <span>{{ __('pages/home.nav.me') }}</span>
    </a>
  </div>
</nav>
