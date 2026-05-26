{{-- A bracket match cell. Expects $m with FIFA-shaped fields. $big = optional. --}}
@php
    /*
     * Bracket dates are stored as UTC strings in WorldCupDraw, so parse as
     * UTC and convert to Phnom Penh time (Asia/Phnom_Penh, UTC+7).
     */
    $big      = $big ?? false;
    $kickoff  = \Illuminate\Support\Carbon::parse($m['date'], 'UTC')->setTimezone('Asia/Phnom_Penh');
    $dateText = $kickoff->format('d M');
    $timeText = $kickoff->format('H:i');
@endphp
<article class="brk {{ $big ? 'brk--big' : '' }}" data-mid="{{ $m['id'] }}">
    <header class="brk__hd">
        <span class="brk__date">
            <span class="brk__date-d">{{ $dateText }}</span>
            <span class="brk__date-t">{{ $timeText }}</span>
        </span>
        <span class="brk__id">{{ $m['id'] }}</span>
    </header>
    <div class="brk__teams">
        <div class="brk__team" data-side="home">
            <span class="brk__flag" data-flag>
                <svg viewBox="0 0 24 18" width="22" height="16" aria-hidden="true">
                    <rect width="24" height="18" rx="1" fill="currentColor" opacity=".08"/>
                    <path d="M3 4h18v3H3zM3 11h18v3H3z" fill="currentColor" opacity=".18"/>
                </svg>
            </span>
            <span class="brk__name is-placeholder" data-name>{{ $m['home'] }}</span>
            <span class="brk__score" data-score>–</span>
        </div>
        <div class="brk__team" data-side="away">
            <span class="brk__flag" data-flag>
                <svg viewBox="0 0 24 18" width="22" height="16" aria-hidden="true">
                    <rect width="24" height="18" rx="1" fill="currentColor" opacity=".08"/>
                    <path d="M3 4h18v3H3zM3 11h18v3H3z" fill="currentColor" opacity=".18"/>
                </svg>
            </span>
            <span class="brk__name is-placeholder" data-name>{{ $m['away'] }}</span>
            <span class="brk__score" data-score>–</span>
        </div>
    </div>
    <footer class="brk__ft">
        <span class="brk__status" data-status>Upcoming</span>
    </footer>
</article>
