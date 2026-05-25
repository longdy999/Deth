@extends('worldcup.layout')

@section('title', 'Knockout Bracket — FIFA World Cup 26')

@php
    /*
     * Build the Cambodia-time strings for the page header and the
     * champion banner from the actual stored UTC dates so they always
     * match the bracket cell labels.
     */
    $tz       = 'Asia/Phnom_Penh';
    $r32First = $bracket['r32'][0]['date'] ?? null;
    $finalDt  = $bracket['final'][0]['date'] ?? null;

    $startICT = $r32First ? \Illuminate\Support\Carbon::parse($r32First, 'UTC')->setTimezone($tz) : null;
    $finalICT = $finalDt  ? \Illuminate\Support\Carbon::parse($finalDt,  'UTC')->setTimezone($tz) : null;

    $rangeText = ($startICT && $finalICT)
        ? $startICT->format('d M') . ' – ' . $finalICT->format('d M Y')
        : '28 Jun – 20 Jul 2026';

    $finalInfo = $finalICT
        ? 'Final · ' . $finalICT->format('d M Y · H:i') . ' ICT · MetLife Stadium, New York/New Jersey'
        : 'Final · MetLife Stadium, New York/New Jersey';
@endphp

@section('content')
<main class="main--wide">

    <header class="page-head">
        <div>
            <h1 class="page-head__title">Knockout Bracket</h1>
            <p class="page-head__sub">Round of 32 → Final · {{ $rangeText }}</p>
        </div>
        <div class="page-head__actions">
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

    {{-- ====== CHAMPION BANNER ====== --}}
    <section class="panel panel--bracket" id="bracket">
        <div class="champion" id="champion-card">
            <div class="champion__shine" aria-hidden="true"></div>
            <div class="champion__inner">
                <div class="champion__trophy" aria-hidden="true">
                    <svg viewBox="0 0 64 64" width="56" height="56" focusable="false">
                        <defs>
                            <linearGradient id="trophy-g" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0" stop-color="#ffe27a"/>
                                <stop offset=".5" stop-color="#e3b033"/>
                                <stop offset="1" stop-color="#7a4f12"/>
                            </linearGradient>
                        </defs>
                        <path fill="url(#trophy-g)"
                              d="M18 8h28a2 2 0 0 1 2 2v6h5a4 4 0 0 1 4 4v6a9 9 0 0 1-9 9h-2.7A16 16 0 0 1 35 49.6V54h6a2 2 0 0 1 2 2v2H21v-2a2 2 0 0 1 2-2h6v-4.4A16 16 0 0 1 16.7 35H14a9 9 0 0 1-9-9v-6a4 4 0 0 1 4-4h5v-6a2 2 0 0 1 2-2zm30 12v12h.5a5 5 0 0 0 5-5v-5a2 2 0 0 0-2-2H48zm-32 0H10a2 2 0 0 0-2 2v5a5 5 0 0 0 5 5h3V20z"/>
                    </svg>
                </div>
                <div class="champion__body">
                    <div class="champion__label">FIFA World Cup 2026 Champion</div>
                    <div class="champion__team" id="champion-team">
                        <span class="champion__flag" data-flag>
                            <svg viewBox="0 0 24 18" width="36" height="27" aria-hidden="true">
                                <rect width="24" height="18" rx="1.5" fill="currentColor" opacity=".12"/>
                            </svg>
                        </span>
                        <span class="champion__name" data-name>To Be Decided</span>
                    </div>
                    <div class="champion__final-info">
                        {{ $finalInfo }}
                    </div>
                </div>
                @if(!empty($league['strBadge']))
                    <div class="champion__logo" aria-hidden="true">
                        <img src="{{ $league['strBadge'] }}" alt="">
                    </div>
                @endif
            </div>
        </div>

        {{-- Desktop bracket --}}
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

        {{-- Mobile bracket --}}
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
@endsection
