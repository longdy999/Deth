<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>FIFA World Cup 26 — Match Center</title>
    <link rel="preconnect" href="https://flagcdn.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/worldcup.css') }}">
    {{-- Set theme as early as possible to avoid flash --}}
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
                {{-- FIFA-style trophy mark (inline SVG) --}}
                <svg viewBox="0 0 32 32" width="32" height="32" focusable="false">
                    <defs>
                        <linearGradient id="cup-g" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0" stop-color="#f5d067"/>
                            <stop offset="1" stop-color="#b8862b"/>
                        </linearGradient>
                    </defs>
                    <path fill="url(#cup-g)"
                          d="M9 4h14a1 1 0 0 1 1 1v3h2.5A2.5 2.5 0 0 1 29 10.5v3A4.5 4.5 0 0 1 24.5 18H24v1a8 8 0 0 1-6 7.74V28h3a1 1 0 0 1 1 1v1H10v-1a1 1 0 0 1 1-1h3v-1.26A8 8 0 0 1 8 19v-1h-.5A4.5 4.5 0 0 1 3 13.5v-3A2.5 2.5 0 0 1 5.5 8H8V5a1 1 0 0 1 1-1Zm15 6v6a2.5 2.5 0 0 0 2.5-2.5v-3a.5.5 0 0 0-.5-.5H24Zm-16 0H5.5a.5.5 0 0 0-.5.5v3A2.5 2.5 0 0 0 7.5 16H8v-6Z"/>
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
            <a href="#recent">Results</a>
        </nav>

        <div class="topbar__right">
            <span class="status" id="status" aria-live="polite">
                <span id="status-dot" class="dot"></span>
                <span id="status-text">connecting…</span>
            </span>
            <button id="theme-toggle"
                    class="theme-toggle"
                    type="button"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode">
                {{-- Sun (shown in dark mode) --}}
                <svg class="icon icon--sun" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <circle cx="12" cy="12" r="4" fill="currentColor"/>
                    <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 3v2"/><path d="M12 19v2"/><path d="M3 12h2"/><path d="M19 12h2"/>
                        <path d="M5.6 5.6l1.4 1.4"/><path d="M17 17l1.4 1.4"/>
                        <path d="M5.6 18.4l1.4-1.4"/><path d="M17 7l1.4-1.4"/>
                    </g>
                </svg>
                {{-- Moon (shown in light mode) --}}
                <svg class="icon icon--moon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <path fill="currentColor"
                          d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
                </svg>
            </button>
        </div>
    </div>
</header>

<main>

    {{-- ==================== HERO / FEATURED MATCH ==================== --}}
    <section class="hero" id="featured-panel">
        <div class="hero__chip">Match Center</div>
        <div class="hero__inner" id="featured-card">
            {{-- Filled by JS. Shows a graceful skeleton until first poll arrives. --}}
            <div class="hero__skeleton">
                <div class="hero__sk-team"></div>
                <div class="hero__sk-score"></div>
                <div class="hero__sk-team"></div>
            </div>
        </div>
    </section>

    {{-- ==================== UP NEXT ==================== --}}
    <section class="panel" id="upcoming-panel">
        <header class="panel__head">
            <h2 class="panel__title">Up Next</h2>
            <span class="panel__sub" id="upcoming-count"></span>
        </header>
        <div id="upcoming-grid" class="match-grid match-grid--3">
            {{-- 3 skeleton placeholders --}}
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
            <div class="match match--sk"></div>
        </div>
    </section>

    {{-- ==================== RECENT RESULTS ==================== --}}
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

    {{-- ==================== STANDINGS ==================== --}}
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

    {{-- ==================== KNOCKOUT BRACKET ==================== --}}
    <section class="panel panel--bracket" id="bracket">
        <header class="panel__head">
            <h2 class="panel__title">Knockout Bracket</h2>
            <span class="panel__sub">Round of 32 → Final</span>
        </header>

        <div class="bracket-desktop">
            <div class="bracket-scroll">
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
