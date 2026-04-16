@props([
    'showLeft' => true,
    'showRight' => true,
    'useSplitLayout' => true,
    'wrapperClass' => 'mt-5 rounded-xl border border-theme bg-theme-secondary/60 p-4',
])

@php
    $hasBothSections = $showLeft && $showRight;
@endphp

@if ($useSplitLayout)
    @if ($showLeft || $showRight)
        <div {{ $attributes->class([$wrapperClass]) }}>
            @isset($top)
                <div class="mb-3 border-b border-theme pb-3">
                    {{ $top }}
                </div>
            @endisset

            <div class="{{ $hasBothSections ? 'grid grid-cols-2 gap-3' : 'grid grid-cols-1 gap-3' }}">
                @if ($showLeft)
                    <div @class(['min-w-0', 'pr-3' => $hasBothSections])>
                        {{ $left ?? '' }}
                    </div>
                @endif

                @if ($showRight)
                    <div @class(['min-w-0', 'border-l border-theme pl-3' => $hasBothSections])>
                        {{ $right ?? '' }}
                    </div>
                @endif
            </div>
        </div>
    @endif
@else
    <div {{ $attributes->class([$wrapperClass]) }}>
        {{ $slot }}
    </div>
@endif
