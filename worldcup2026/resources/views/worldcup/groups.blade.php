@extends('worldcup.layout')

@section('title', 'Group Stage — FIFA World Cup 26')

@section('content')
<main>

    <header class="page-head">
        <div>
            <h1 class="page-head__title">Group Stage</h1>
            <p class="page-head__sub">12 groups of 4 · top 2 + 8 best 3rd-placed teams advance to the Round of 32</p>
        </div>
        <div class="page-head__legend">
            <span><span class="legend-dot legend-dot--qual"></span> Qualifies</span>
            <span><span class="legend-dot legend-dot--3rd"></span> 3rd-place playoff</span>
        </div>
    </header>

    <div class="groups-grid" id="standings">
        @foreach($groups as $letter => $teams)
            @php($matches = $groupMatches[$letter] ?? [])
            <article class="group-card" data-group="{{ $letter }}">
                <header class="group-card__head">
                    <h2>Group {{ $letter }}</h2>
                    <span class="group-card__sub">{{ count($teams) }} teams</span>
                </header>

                <div class="group-card__table-wrap">
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($matches))
                    <div class="group-card__matches">
                        <h3 class="group-card__matches-title">Fixtures</h3>
                        @foreach($matches as $m)
                            <div class="gmatch">
                                <span class="gmatch__date">
                                    @php($d = \Illuminate\Support\Carbon::parse($m['kickoff'] ?? null))
                                    {{ $d ? $d->format('d M · H:i') : '' }}
                                </span>
                                <span class="gmatch__teams">
                                    @if(!empty($m['home_iso']))
                                        <img src="https://flagcdn.com/w20/{{ $m['home_iso'] }}.png" alt="">
                                    @endif
                                    <span>{{ $m['home'] ?? 'TBD' }}</span>
                                    <em class="gmatch__score">
                                        @if($m['home_score'] !== null && $m['away_score'] !== null)
                                            {{ $m['home_score'] }}–{{ $m['away_score'] }}
                                        @else
                                            vs
                                        @endif
                                    </em>
                                    <span>{{ $m['away'] ?? 'TBD' }}</span>
                                    @if(!empty($m['away_iso']))
                                        <img src="https://flagcdn.com/w20/{{ $m['away_iso'] }}.png" alt="">
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    <p class="legend">
        <strong>P</strong>=Played &nbsp;
        <strong>W</strong>=Wins &nbsp;
        <strong>D</strong>=Draws &nbsp;
        <strong>L</strong>=Losses &nbsp;
        <span class="hide-md"><strong>GF</strong>=Goals For &nbsp;<strong>GA</strong>=Goals Against &nbsp;</span>
        <span class="hide-sm"><strong>GD</strong>=Goal Difference &nbsp;</span>
        <strong>Pts</strong>=Points
    </p>

</main>
@endsection
