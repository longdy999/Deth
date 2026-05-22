<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FIFA World Cup 2026 — Live Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/worldcup.css') }}">
</head>
<body>
<header class="topbar">
    <div class="brand">
        @if(!empty($league['strBadge']))
            <img src="{{ $league['strBadge'] }}" alt="" class="brand__logo">
        @endif
        <div>
            <h1>FIFA World Cup 2026</h1>
            <p class="brand__sub">Live tracker · powered by TheSportsDB</p>
        </div>
    </div>
    <div class="status">
        <span id="status-dot" class="dot"></span>
        <span id="status-text">connecting…</span>
        <span class="muted" id="last-updated"></span>
    </div>
</header>

<main>
    <section class="panel" id="live-panel">
        <h2>Live &amp; Up Next</h2>
        <div id="live-grid" class="match-grid"><div class="empty">Loading…</div></div>
    </section>

    <section class="panel">
        <h2>Recent Results</h2>
        <div id="recent-grid" class="match-grid"><div class="empty">Loading…</div></div>
    </section>

    <section class="panel">
        <h2 id="groups-title">Group Stage</h2>
        <div id="groups" class="groups"><div class="empty">Standings will appear once group matches are played.</div></div>
    </section>

    <section class="panel">
        <h2>Knockout Bracket</h2>
        <div id="bracket" class="bracket"><div class="empty">Bracket will populate after the group stage.</div>
        </div>
    </section>
</main>

<footer class="foot">
    <span>Data: TheSportsDB free API · cached server-side, polled every 10s</span>
</footer>

<script>
    window.SNAPSHOT_URL = "{{ route('worldcup.snapshot') }}";
</script>
<script src="{{ asset('js/worldcup.js') }}"></script>
</body>
</html>
