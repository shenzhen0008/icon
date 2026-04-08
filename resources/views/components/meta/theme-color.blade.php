@php
  $resolvedTheme = request()->cookie('theme', config('themes.active', 'tech'));
  $themeColor = $resolvedTheme === 'business' ? '#f3f4f6' : '#0f172a';
@endphp
<meta name="theme-color" content="{{ $themeColor }}">
