@props([
    'products' => [],
])

@if (count($products) > 0)
    <section class="grid gap-4 md:grid-cols-2">
        @foreach ($products as $product)
            <article class="rounded-xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 p-6">
                @if (!empty($product['code']))
                    <p class="mb-1 text-scale-body text-cyan-200">{{ $product['code'] }}</p>
                @endif
                <h2 class="text-scale-title font-semibold text-white">{{ $product['name'] ?? '--' }}</h2>
                @if (!empty($product['description']))
                    <p class="mt-3 text-scale-body text-slate-300">{{ $product['description'] }}</p>
                @endif
            </article>
        @endforeach
    </section>
@endif
