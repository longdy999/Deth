<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FIFA World Cup 2026 — Standings &amp; Bracket</title>
    <link rel="preconnect" href="https://flagcdn.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/worldcup.css') }}">
</head>
<body>

<header class="topbar">
    <div class="brand">
        @if(!empty($league['strBadge']))
            <img src="{{ $league['strBadge'] }}" alt="" class="brand__logo">
        @endif
        <div>
            <h1>FIFA World Cup 26</h1>
            <p class="brand__sub">Canada · Mexico · USA · 11 June – 19 July 2026</p>
        </div>
    </div>
    <div class="status">
        <span id="status-dot" class="dot"></span>
        <span id="status-text">connecting…</span>
        <span class="muted" id="last-updated"></span>
    </div>
</header>

<main>

    {{-- Live banner (hidden until live) --}}
    <section class="panel" id="live-panel" hidden>
        <h2 class="panel__title">Live Now</h2>
        <div id="live-grid" class="match-grid"></div>
    </section>

    {{-- Upcoming --}}
    <section class="panel" id="upcoming-panel" hidden>
        <h2 class="panel__title">Up Next</h2>
        <div id="upcoming-grid" class="match-grid"></div>
    </section>

    {{-- STANDINGS --}}
    <section class="panel">
        <h2 class="panel__title">Standings</h2>

        <div class="standings" id="standings">
            @foreach($groups as $letter => $teams)
                <article class="group" data-group="{{ $letter }}">
                    <header class="group__head">
                        <h3>Group {{ $letter }}</h3>
                        <div class="group__cols" aria-hidden="true">
                            <span title="Played">P</span>
                            <span title="Wins">W</span>
                            <span title="Draws">D</span>
                            <span title="Losses">L</span>
                            <span class="hide-sm" title="Goals For">GF</span>
                            <span class="hide-sm" title="Goals Against">GA</span>
                            <span class="hide-xs" title="Goal Difference">GD</span>
                            <span class="pts" title="Points">Pts</span>
                            <span class="form-h hide-xs" title="Form (last 5)">Form</span>
                        </div>
                    </header>

                    <ol class="group__rows">
                        @foreach($teams as $i => $t)
                            <li class="row" data-team="{{ $t['team'] }}" data-rank="{{ $i + 1 }}">
                                <span class="rank">{{ $i + 1 }}</span>
                                <span class="flag">
                                    <img src="https://flagcdn.com/w40/{{ $t['iso'] }}.png" alt="" loading="lazy">
                                </span>
                                <span class="team">{{ $t['team'] }}</span>
                                <span class="stat" data-stat="mp">0</span>
                                <span class="stat" data-stat="w">0</span>
                                <span class="stat" data-stat="d">0</span>
                                <span class="stat" data-stat="l">0</span>
                                <span class="stat hide-sm" data-stat="gf">0</span>
                                <span class="stat hide-sm" data-stat="ga">0</span>
                                <span class="stat hide-xs" data-stat="gd">0</span>
                                <span class="stat pts" data-stat="pts">0</span>
                                <span class="form hide-xs" data-form>
                                    <span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach
        </div>

        <p class="legend">
            <strong>P</strong>=Played &nbsp;
            <strong>W</strong>=Wins &nbsp;
            <strong>D</strong>=Draws &nbsp;
            <strong>L</strong>=Losses &nbsp;
            <span class="hide-sm"><strong>GF</strong>=Goals For &nbsp;<strong>GA</strong>=Goals Against &nbsp;</span>
            <span class="hide-xs"><strong>GD</strong>=Goal Difference &nbsp;</span>
            <strong>Pts</strong>=Points
            <span class="hide-xs">
                <br>
                <strong>Form:</strong>
                <span class="dot-form is-w" title="Win"></span> Win
                <span class="dot-form is-d" title="Draw"></span> Draw
                <span class="dot-form is-l" title="Loss"></span> Loss
                <span class="dot-form" title="Not played">–</span> Not played
            </span>
        </p>
    </section>

    {{-- KNOCKOUT BRACKET --}}
    <section class="panel panel--bracket">
        <h2 class="panel__title">Knockout bracket</h2>
        <p class="bracket-hint" id="bracket-hint">Scroll horizontally →</p>

        <div class="bracket-wrap" id="bracket-wrap">
            {{-- Round headers, scroll-synced with the bracket below --}}
            <ol class="bracket-headers" aria-hidden="true">
                <li>Round of 32</li>
                <li>Round of 16</li>
                <li>Quarter-final</li>
                <li>Semi-final</li>
                <li>Final</li>
                <li>Semi-final</li>
                <li>Quarter-final</li>
                <li>Round of 16</li>
                <li>Round of 32</li>
            </ol>

            <div class="bracket" id="bracket">
                {{-- Left side: R32 → R16 → QF → SF --}}
                <div class="col col--r32" data-round="r32-l">
                    @foreach(array_slice($bracket['r32'], 0, 8) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
                <div class="col col--r16" data-round="r16-l">
                    @foreach(array_slice($bracket['r16'], 0, 4) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
                <div class="col col--qf" data-round="qf-l">
                    @foreach(array_slice($bracket['qf'], 0, 2) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
                <div class="col col--sf" data-round="sf-l">
                    @include('worldcup.partials.match', ['m' => $bracket['sf'][0]])
                </div>

                {{-- Center: Final + 3rd place --}}
                <div class="col col--final" data-round="final">
                    <div class="final-stack">
                        <div class="round-label">Final</div>
                        @include('worldcup.partials.match', ['m' => $bracket['final'][0], 'big' => true])

                        <div class="round-label round-label--small">Play-off for third place</div>
                        @include('worldcup.partials.match', ['m' => $bracket['third'][0]])
                    </div>
                </div>

                {{-- Right side: SF → QF → R16 → R32 --}}
                <div class="col col--sf" data-round="sf-r">
                    @include('worldcup.partials.match', ['m' => $bracket['sf'][1]])
                </div>
                <div class="col col--qf" data-round="qf-r">
                    @foreach(array_slice($bracket['qf'], 2, 2) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
                <div class="col col--r16" data-round="r16-r">
                    @foreach(array_slice($bracket['r16'], 4, 4) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
                <div class="col col--r32" data-round="r32-r">
                    @foreach(array_slice($bracket['r32'], 8, 8) as $m)
                        @include('worldcup.partials.match', ['m' => $m])
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Recent results --}}
    <section class="panel" id="recent-panel" hidden>
        <h2 class="panel__title">Recent Results</h2>
        <div id="recent-grid" class="match-grid"></div>
    </section>

</main>

<footer class="foot">
    Data: <a href="https://www.thesportsdb.com" rel="noopener">TheSportsDB</a> free API ·
    flags by <a href="https://flagcdn.com" rel="noopener">flagcdn.com</a> ·
    cached server-side, polled every 10 s
</footer>

<script>
    window.SNAPSHOT_URL = "{{ route('worldcup.snapshot') }}";
</script>
<script src="{{ asset('js/worldcup.js') }}"></script>
</body>
</html>
