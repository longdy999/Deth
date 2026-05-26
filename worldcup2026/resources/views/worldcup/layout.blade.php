<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>@yield('title', 'FIFA World Cup 26')</title>
    <link rel="preconnect" href="https://flagcdn.com" crossorigin>
    @if(!empty($league['strBadge']))
        <link rel="icon" href="{{ $league['strBadge'] }}" type="image/png">
    @endif
    <link rel="stylesheet" href="{{ asset('css/worldcup.css') }}">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('wc-theme');
                var sys   = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.dataset.theme = saved || sys;
            } catch (e) {}
        })();
    </script>
    @stack('head')
</head>
<body data-page="{{ $page ?? 'home' }}">

<header class="topbar">
    <div class="topbar__inner">
        <a class="brand" href="{{ route('worldcup.index') }}" aria-label="FIFA World Cup 26 home">
            <span class="brand__mark" aria-hidden="true">
                @if(!empty($league['strBadge']))
                    <img src="{{ $league['strBadge'] }}"
                         alt="FIFA World Cup"
                         class="brand__logo-img"
                         loading="eager"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                @endif
                <svg class="brand__logo-fallback" viewBox="0 0 64 64" width="44" height="44"
                     focusable="false" aria-hidden="true"
                     style="{{ !empty($league['strBadge']) ? 'display:none' : '' }}">
                    <defs>
                        <linearGradient id="cup-g" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0" stop-color="#fde68a"/>
                            <stop offset=".55" stop-color="#d4a64a"/>
                            <stop offset="1" stop-color="#7a5320"/>
                        </linearGradient>
                    </defs>
                    <path fill="url(#cup-g)" d="M18 8h28a2 2 0 0 1 2 2v6h5a4 4 0 0 1 4 4v6a9 9 0 0 1-9 9h-2.7A16 16 0 0 1 35 49.6V54h6a2 2 0 0 1 2 2v2H21v-2a2 2 0 0 1 2-2h6v-4.4A16 16 0 0 1 16.7 35H14a9 9 0 0 1-9-9v-6a4 4 0 0 1 4-4h5v-6a2 2 0 0 1 2-2zm30 12v12h.5a5 5 0 0 0 5-5v-5a2 2 0 0 0-2-2H48zm-32 0H10a2 2 0 0 0-2 2v5a5 5 0 0 0 5 5h3V20z"/>
                </svg>
            </span>
            <span class="brand__text">
                <strong>FIFA World Cup 26</strong>
                <small>Canada · Mexico · USA · 11 Jun – 19 Jul 2026</small>
            </span>
        </a>

        <nav class="nav" aria-label="Sections">
            <a href="{{ route('worldcup.index') }}"   class="{{ ($page ?? 'home') === 'home'    ? 'is-active' : '' }}">Home</a>
            <a href="{{ route('worldcup.groups') }}"  class="{{ ($page ?? '') === 'groups'    ? 'is-active' : '' }}">Group Stage</a>
            <a href="{{ route('worldcup.bracket') }}" class="{{ ($page ?? '') === 'bracket'   ? 'is-active' : '' }}">Knockout</a>
        </nav>

        <div class="topbar__right">
            <span class="status" id="status" aria-live="polite">
                <span id="status-dot" class="dot"></span>
                <span id="status-text">connecting…</span>
            </span>
            <button id="theme-toggle"
                    class="icon-btn"
                    type="button"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode">
                <svg class="icon icon--sun" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <circle cx="12" cy="12" r="4" fill="currentColor"/>
                    <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 3v2"/><path d="M12 19v2"/><path d="M3 12h2"/><path d="M19 12h2"/>
                        <path d="M5.6 5.6l1.4 1.4"/><path d="M17 17l1.4 1.4"/>
                        <path d="M5.6 18.4l1.4-1.4"/><path d="M17 7l1.4-1.4"/>
                    </g>
                </svg>
                <svg class="icon icon--moon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
                </svg>
            </button>
        </div>
    </div>
</header>

@yield('content')

<footer class="foot">
    <span>Data: <a href="https://www.thesportsdb.com" rel="noopener">TheSportsDB</a> ·
    Flags: <a href="https://flagcdn.com" rel="noopener">flagcdn.com</a> ·
    All times shown in <strong>Phnom Penh time (UTC+7)</strong></span>
    <span class="muted" id="last-updated"></span>
</footer>

<script>
    window.SNAPSHOT_URL = "{{ route('worldcup.snapshot') }}";
    window.WC_PAGE = "{{ $page ?? 'home' }}";
</script>
<script src="{{ asset('js/worldcup.js') }}"></script>
@stack('scripts')
</body>
</html>
