<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
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
        <div class="brand__text">
            <h1>FIFA World Cup 26</h1>
            <p class="brand__sub">Canada · Mexico · USA · 11 Jun – 19 Jul 2026</p>
        </div>
    </div>
    <div class="status">
        <span id="status-dot" class="dot"></span>
        <span id="status-text">connecting…</span>
        <span class="muted" id="last-updated"></span>
    </div>
</header>

<main>

    {{-- Live (hidden until live) --}}
    <section class="panel" id="live-panel" hidden>
        <h2 class="panel__title">Live Now</h2>
        <div id="live-grid" class="match-grid"></div>
    </section>

    {{-- Up Next --}}
    <section class="panel" id="upcoming-panel" hidden>
        <h2 class="panel__title">Up Next</h2>
        <div id="upcoming-grid" class="match-grid"></div>
    </section>

    {{-- ==================== STANDINGS ==================== --}}
    <section class="panel">
        <h2 class="panel__title">Standings</h2>

        <div class="standings" id="standings">
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
                <span class="dot-form is-w" title="Win"></span> W
                <span class="dot-form is-d" title="Draw"></span> D
                <span class="dot-form is-l" title="Loss"></span> L
            </span>
        </p>
    </section>

    {{-- ==================== KNOCKOUT BRACKET ==================== --}}
    <section class="panel panel--bracket">
        <h2 class="panel__title">Knockout bracket</h2>

        {{-- Desktop bracket (>=1024px): FIFA-style horizontal tree --}}
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

        {{-- Mobile bracket (<1024px): vertical stack, one round at a time --}}
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
