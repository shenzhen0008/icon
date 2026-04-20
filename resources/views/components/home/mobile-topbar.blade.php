<header class="fixed inset-x-0 top-0 z-30 border-b border-white/10 bg-slate-950/95 backdrop-blur md:hidden">
    <div class="mx-auto flex w-full max-w-4xl items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-400/20 text-scale-micro font-bold text-cyan-200 ring-1 ring-cyan-300/30">
                IM
            </span>
            <span class="text-scale-ui font-semibold tracking-[0.16em] text-cyan-300">{{ config('app.name') }}</span>
        </div>
        <button
            type="button"
            class="rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-3 py-1.5 text-scale-micro font-medium text-cyan-200 hover:bg-cyan-400/20"
        >
            {{ __('pages/login.title') }}
        </button>
    </div>
</header>
