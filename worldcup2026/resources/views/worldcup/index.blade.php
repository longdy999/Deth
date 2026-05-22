<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>FIFA World Cup 26 — Match Center</title>
    <link rel="preconnect" href="https://flagcdn.com" crossorigin>
    @if(!empty($league['strBadge']))
        <link rel="icon" href="{{ $league['strBadge'] }}" type="image/png">
    @endif
    <link rel="stylesheet" href="{{ asset('css/worldcup.css') }}">
    {{-- Apply theme as early as possible to avoid flash --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('wc-theme');
                var sys   = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.dataset.theme = saved || sys;
            } catch (e) {}
        })();
    </script>
</head>
<body>

<header class="topbar">
    <div class="topbar__inner">
        <a class="brand" href="/" aria-label="FIFA World Cup 26 home">
            <span class="brand__mark" aria-hidden="true">
                @if(!empty($league['strBadge']))
                    <img src="{{ $league['strBadge'] }}"
                         alt="FIFA World Cup"
                         class="brand__logo-img"
                         loading="eager"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                @endif
                {{-- Inline SVG fallback for the FIFA World Cup mark --}}
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
            <a href="#standings">Standings</a>
            <a href="#bracket">Bracket</a>
            <a href="#recent-panel">Results</a>
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

<main>

    {{-- HERO / FEATURED MATCH --}}
    <section class="hero" id="featured-panel">
        <div class="hero__chip">Match Center</div>
        <div class="hero__inner" id="featured-card">
            <div class="hero__skeleton">
                <div class="hero__sk-team"></div>
                <div class="hero__sk-score"></div>
                <div class="hero__sk-team"></div>
            </div>
        </div>
    </section>

    {{-- UP NEXT --}}
    <section class="panel" id="upcoming-panel">
        <header class="panel__head">
            <h2 class="panel__title">Up Next</h2>
            <span class="panel__sub" id="upcoming-count"></span>
        </header>
        <div id="upcoming-grid" class="match-grid match-grid--3">
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
        </div>
    </section>

    {{-- RECENT RESULTS --}}
    <section class="panel" id="recent-panel">
        <header class="panel__head">
            <h2 class="panel__title">Recent Results</h2>
            <span class="panel__sub" id="recent-count"></span>
        </header>
        <div id="recent-grid" class="match-grid match-grid--3">
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
        </div>
    </section>

    {{-- STANDINGS --}}
    <section class="panel" id="standings">
        <header class="panel__head">
            <h2 class="panel__title">Standings</h2>
            <span class="panel__sub">Top 2 of each group + 8 best 3rds advance</span>
        </header>

        <div class="standings">
            @foreach($groups as $letter => $teams)
                <article class="group" data-group="{{ $letter }}">
                    <header class="group__head">
                        <h3>Group {{ $letter }}</h3>
                    </header>
                    <div class="group__table-wrap">
                        <table class="group__table">
                            <thead>
                                <tr>
                                    <th class="col-rank" scope="col">#</th>
                                    <th class="col-team" scope="col">Team</th>
                                    <th class="col-stat" scope="col" title="Played">P</th>
                                    <th class="col-stat" scope="col" title="Wins">W</th>
                                    <th class="col-stat" scope="col" title="Draws">D</th>
                                    <th class="col-stat" scope="col" title="Losses">L</th>
                                    <th class="col-stat hide-md" scope="col" title="Goals For">GF</th>
                                    <th class="col-stat hide-md" scope="col" title="Goals Against">GA</th>
                                    <th class="col-stat hide-sm" scope="col" title="Goal Difference">GD</th>
                                    <th class="col-pts" scope="col" title="Points">Pts</th>
                                    <th class="col-form hide-sm" scope="col" title="Form (last 5)">Form</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teams as $i => $t)
                                    <tr class="row" data-team="{{ $t['team'] }}" data-rank="{{ $i + 1 }}">
                                        <td class="col-rank rank">{{ $i + 1 }}</td>
                                        <td class="col-team">
                                            <span class="flag">
                                                <img src="https://flagcdn.com/w40/{{ $t['iso'] }}.png" alt="" loading="lazy">
                                            </span>
                                            <span class="team">{{ $t['team'] }}</span>
                                        </td>
                                        <td class="col-stat" data-stat="mp">0</td>
                                        <td class="col-stat" data-stat="w">0</td>
                                        <td class="col-stat" data-stat="d">0</td>
                                        <td class="col-stat" data-stat="l">0</td>
                                        <td class="col-stat hide-md" data-stat="gf">0</td>
                                        <td class="col-stat hide-md" data-stat="ga">0</td>
                                        <td class="col-stat hide-sm" data-stat="gd">0</td>
                                        <td class="col-pts" data-stat="pts">0</td>
                                        <td class="col-form hide-sm">
                                            <span class="form" data-form>
                                                <span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span><span class="dot-form">–</span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="legend">
            <strong>P</strong>=Played &nbsp;
            <strong>W</strong>=Wins &nbsp;
            <strong>D</strong>=Draws &nbsp;
            <strong>L</strong>=Losses &nbsp;
            <span class="hide-md"><strong>GF</strong>=Goals For &nbsp;<strong>GA</strong>=Against &nbsp;</span>
            <span class="hide-sm"><strong>GD</strong>=Goal Diff &nbsp;</span>
            <strong>Pts</strong>=Points
            <span class="hide-sm">
                · <strong>Form:</strong>
                <span class="dot-form is-w"></span> W
                <span class="dot-form is-d"></span> D
                <span class="dot-form is-l"></span> L
            </span>
        </p>
    </section>

    {{-- KNOCKOUT BRACKET --}}
    <section class="panel panel--bracket" id="bracket">
        <header class="panel__head panel__head--bracket">
            <div>
                <h2 class="panel__title">Knockout Bracket</h2>
                <span class="panel__sub">Round of 32 → Final</span>
            </div>
            <div class="panel__actions">
                <button id="fs-enter" class="btn-fs" type="button" title="Open bracket in full screen">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                        <path fill="currentColor" d="M5 9V5h4v2H7v2H5zm0 6h2v2h2v2H5v-4zm14 0v4h-4v-2h2v-2h2zm-4-8V5h4v4h-2V7h-2z"/>
                    </svg>
                    <span>Full screen</span>
                </button>
                <button id="fs-exit" class="btn-fs btn-fs--exit" type="button" title="Exit full screen">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                        <path fill="currentColor" d="M9 5h2v4H7V7h2V5zM5 13h4v4H7v2H5v-4zm10 0v4h-2v-2h-2v-2h4zm0-8V3h2v2h2v2h-4z"/>
                    </svg>
                    <span>Exit full screen</span>
                </button>
            </div>
        </header>

        <div class="bracket-desktop">
            <div class="bracket-scroll">
                <div class="bracket-inner">
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
                    <div class="bracket">
                        <div class="col col--r32">
                            @foreach(array_slice($bracket['r32'], 0, 8) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                        <div class="col col--r16">
                            @foreach(array_slice($bracket['r16'], 0, 4) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                        <div class="col col--qf">
                            @foreach(array_slice($bracket['qf'], 0, 2) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                        <div class="col col--sf">
                            @include('worldcup.partials.match', ['m' => $bracket['sf'][0]])
                        </div>
                        <div class="col col--final">
                            <div class="final-stack">
                                <div class="round-label">Final</div>
                                @include('worldcup.partials.match', ['m' => $bracket['final'][0], 'big' => true])
                                <div class="round-label round-label--small">Play-off for third place</div>
                                @include('worldcup.partials.match', ['m' => $bracket['third'][0]])
                            </div>
                        </div>
                        <div class="col col--sf">
                            @include('worldcup.partials.match', ['m' => $bracket['sf'][1]])
                        </div>
                        <div class="col col--qf">
                            @foreach(array_slice($bracket['qf'], 2, 2) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                        <div class="col col--r16">
                            @foreach(array_slice($bracket['r16'], 4, 4) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                        <div class="col col--r32">
                            @foreach(array_slice($bracket['r32'], 8, 8) as $m)
                                @include('worldcup.partials.match', ['m' => $m])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bracket-mobile">
            @php($mobileRounds = [
                ['title' => 'Round of 32',    'matches' => $bracket['r32']],
                ['title' => 'Round of 16',    'matches' => $bracket['r16']],
                ['title' => 'Quarter-finals', 'matches' => $bracket['qf']],
                ['title' => 'Semi-finals',    'matches' => $bracket['sf']],
                ['title' => 'Third Place',    'matches' => $bracket['third']],
                ['title' => 'Final',          'matches' => $bracket['final']],
            ])
            @foreach($mobileRounds as $r)
                <section class="round-block">
                    <header class="round-block__head">
                        <h3>{{ $r['title'] }}</h3>
                        <span class="round-block__count">{{ count($r['matches']) }} {{ count($r['matches']) === 1 ? 'match' : 'matches' }}</span>
                    </header>
                    <div class="round-block__strip">
                        @foreach($r['matches'] as $m)
                            @include('worldcup.partials.match', ['m' => $m])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </section>

</main>

<footer class="foot">
    <span>Data: <a href="https://www.thesportsdb.com" rel="noopener">TheSportsDB</a> ·
    Flags: <a href="https://flagcdn.com" rel="noopener">flagcdn.com</a> ·
    Cached server-side, polled every 10 s</span>
    <span class="muted" id="last-updated"></span>
</footer>

<script>
    window.SNAPSHOT_URL = "{{ route('worldcup.snapshot') }}";
</script>
<script src="{{ asset('js/worldcup.js') }}"></script>
</body>
</html>
