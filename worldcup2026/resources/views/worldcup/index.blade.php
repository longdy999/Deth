@extends('worldcup.layout')

@section('title', 'FIFA World Cup 26 — Match Center')

@section('content')
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

    {{-- QUICK LINKS to Group Stage / Knockout --}}
    <section class="quicklinks">
        <a class="quicklink" href="{{ route('worldcup.groups') }}">
            <div class="quicklink__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M3 4h7v7H3V4zm0 9h7v7H3v-7zm9-9h9v3h-9V4zm0 5h9v3h-9V9zm0 5h9v3h-9v-3zm0 5h9v2h-9v-2z"/></svg>
            </div>
            <div>
                <strong>Group Stage</strong>
                <small>12 groups · standings · fixtures</small>
            </div>
            <span class="quicklink__arrow">→</span>
        </a>
        <a class="quicklink" href="{{ route('worldcup.bracket') }}">
            <div class="quicklink__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M3 5h6v3H3V5zm0 8h6v3H3v-3zm9-3h6v3h-6v-3zm-9 8h6v3H3v-3z"/></svg>
            </div>
            <div>
                <strong>Knockout Bracket</strong>
                <small>Round of 32 → Final</small>
            </div>
            <span class="quicklink__arrow">→</span>
        </a>
    </section>

</main>
@endsection
