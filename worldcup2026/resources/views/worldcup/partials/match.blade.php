{{-- A bracket match cell. Expects $m with FIFA-shaped fields. $big = optional. --}}
@php($big = $big ?? false)
<article class="brk {{ $big ? 'brk--big' : '' }}" data-mid="{{ $m['id'] }}">
    <header class="brk__hd">
        <span class="brk__date">{{ \Illuminate\Support\Carbon::parse($m['date'])->format('d M · H:i') }}</span>
        <span class="brk__id">{{ $m['id'] }}</span>
    </header>
    <div class="brk__teams">
        <div class="brk__team" data-side="home">
            <span class="brk__flag" data-flag></span>
            <span class="brk__name" data-name>{{ $m['home'] }}</span>
            <span class="brk__score" data-score>–</span>
        </div>
        <div class="brk__team" data-side="away">
            <span class="brk__flag" data-flag></span>
            <span class="brk__name" data-name>{{ $m['away'] }}</span>
            <span class="brk__score" data-score>–</span>
        </div>
    </div>
</article>
