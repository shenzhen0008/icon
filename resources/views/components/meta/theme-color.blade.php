@php
  $themeColor = config('themes.active') === 'business' ? '#f3f4f6' : '#0f172a';
@endphp
<meta id="app-theme-color" name="theme-color" content="{{ $themeColor }}">
<script>
  (() => {
    try {
      const storedThemeForColor = localStorage.getItem('theme');
      const colorMap = { tech: '#0f172a', business: '#f3f4f6' };
      const color = colorMap[storedThemeForColor] || colorMap.tech;
      const meta = document.getElementById('app-theme-color');
      if (meta) meta.setAttribute('content', color);
    } catch (_) {
      // ignore storage errors
    }
  })();
</script>
