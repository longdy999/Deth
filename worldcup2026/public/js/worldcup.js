/* ===========================================================================
 * FIFA World Cup 2026 — light-touch live overlay.
 *
 * The Blade template already renders the full scaffold (12 groups + 32-match
 * bracket). This script only patches it:
 *   1. Updates the stats / form / rank for each team row in place.
 *   2. Resolves bracket slot codes (1E, W74, …) into real teams/scores when
 *      the API has them.
 *   3. Builds match cards for the "Live" and "Recent" banners.
 *
 * It polls /api/snapshot every 10 s — the backend caches everything so we
 * stay well under TheSportsDB's 30-requests/min free-tier limit.
 * =========================================================================== */
(() => {
    const POLL_MS = 10_000;

    const $  = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const dot         = $('#status-dot');
    const statusText  = $('#status-text');
    const lastUpdated = $('#last-updated');

    // ---------------- helpers ----------------

    const setStatus = (state, text) => {
        dot.classList.remove('ok', 'err');
        if (state === 'ok')  dot.classList.add('ok');
        if (state === 'err') dot.classList.add('err');
        statusText.textContent = text;
    };

    const escape = (s) => String(s ?? '').replace(/[&<>"']/g, (c) =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const flagUrl = (iso, size = 'w40') =>
        iso ? `https://flagcdn.com/${size}/${iso}.png` : null;

    // Parse "YYYY-MM-DD HH:MM:SS" or "YYYY-MM-DD HH:MM" into a Date.
    const parseKickoff = (s) => {
        if (!s) return null;
        const d = new Date(s.replace(' ', 'T') + (s.length <= 10 ? '' : ''));
        return isNaN(+d) ? null : d;
    };

    const fmtKickoff = (s) => {
        const d = parseKickoff(s);
        return d ? d.toLocaleString(undefined,
            { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
            : (s || '');
    };

    // Normalize a team name so the SSR row's data-team and the API name line up.
    // Static rows (group SSR) use the canonical names from WorldCupDraw, so the
    // server snapshot returns the same canonical names for groups[*].table[*].team.
    const findRow = (team) => {
        if (!team) return null;
        // Try exact match first; CSS selector doesn't like quotes inside, so use [data-team="…"]
        const escaped = team.replace(/"/g, '\\"');
        return document.querySelector(`.row[data-team="${escaped}"]`);
    };

    // ---------------- Standings overlay ----------------

    const updateStandings = (groups) => {
        if (!Array.isArray(groups)) return;

        for (const g of groups) {
            // Re-rank rows in DOM order according to the sorted API order.
            // The simplest approach: for each team in sorted order, move its
            // existing row to the bottom of its group's <ol>. After all moves,
            // the rows are in the correct order.
            const groupEl = document.querySelector(`.group[data-group="${g.letter}"]`);
            if (!groupEl) continue;
            const list = groupEl.querySelector('.group__rows');
            if (!list) continue;

            (g.table || []).forEach((t, idx) => {
                const row = findRow(t.team);
                if (!row) return;
                row.dataset.rank = String(idx + 1);
                row.querySelector('.rank').textContent = String(idx + 1);

                // Stats
                const setStat = (k, v) => {
                    const el = row.querySelector(`.stat[data-stat="${k}"]`);
                    if (el) el.textContent =
                        (k === 'gd' && v > 0) ? `+${v}` : String(v ?? 0);
                };
                setStat('mp',  t.mp);
                setStat('w',   t.w);
                setStat('d',   t.d);
                setStat('l',   t.l);
                setStat('gf',  t.gf);
                setStat('ga',  t.ga);
                setStat('gd',  t.gd);
                setStat('pts', t.pts);

                // Form: replace 5 dashes with up to 5 colored dots, padded with dashes.
                const form = row.querySelector('[data-form]');
                if (form) {
                    const last5 = (t.form || []).slice(-5);
                    const cells = ['', '', '', '', ''];
                    for (let i = 0; i < 5; i++) {
                        cells[i] = last5[i]
                            ? `<span class="dot-form is-${last5[i].toLowerCase()}"></span>`
                            : `<span class="dot-form">–</span>`;
                    }
                    form.innerHTML = cells.join('');
                }

                // Move row to the new position.
                list.appendChild(row);
            });
        }
    };

    // ---------------- Bracket overlay ----------------

    const updateBracket = (rounds) => {
        if (!Array.isArray(rounds)) return;
        for (const r of rounds) {
            for (const m of (r.matches || [])) {
                const cell = document.querySelector(`.brk[data-mid="${m.id}"]`);
                if (!cell) continue;

                const sides = {
                    home: cell.querySelector('.brk__team[data-side="home"]'),
                    away: cell.querySelector('.brk__team[data-side="away"]'),
                };
                const apply = (side, name, iso, score) => {
                    const el = sides[side];
                    if (!el) return;
                    const nameEl  = el.querySelector('[data-name]');
                    const flagEl  = el.querySelector('[data-flag]');
                    const scoreEl = el.querySelector('[data-score]');

                    if (name) {
                        nameEl.textContent = name;
                        nameEl.classList.remove('is-placeholder');
                    } else {
                        // No real team yet — keep the FIFA slot code, mark as placeholder.
                        nameEl.classList.add('is-placeholder');
                    }
                    flagEl.innerHTML = iso
                        ? `<img src="${flagUrl(iso, 'w20')}" alt="">`
                        : '';
                    scoreEl.textContent =
                        (score === null || score === undefined) ? '–' : String(score);
                };

                apply('home', m.home_team, m.home_iso, m.home_score);
                apply('away', m.away_team, m.away_iso, m.away_score);

                // Highlight winner once both scores are known.
                const hs = m.home_score, as = m.away_score;
                sides.home.classList.toggle('is-winner', hs !== null && as !== null && hs > as);
                sides.away.classList.toggle('is-winner', hs !== null && as !== null && as > hs);
            }
        }
    };

    // ---------------- Live & Recent banners ----------------

    const isLive = (m) => {
        const s = (m.status || '').toLowerCase();
        return ['in play','1h','2h','ht','live'].includes(s);
    };

    const matchCard = (m) => {
        const hs = m.home_score, as = m.away_score;
        const hasScore = hs !== null && hs !== undefined && as !== null && as !== undefined;
        const live = isLive(m);
        const score = hasScore
            ? `<span class="match__score">${hs} – ${as}</span>`
            : `<span class="match__score match__score--vs">${escape(fmtKickoff(m.kickoff))}</span>`;

        const flag = (iso) => iso
            ? `<img src="${flagUrl(iso, 'w40')}" alt="">`
            : '';

        return `
            <article class="match">
                <div class="match__teams">
                    <div class="match__team match__team--home">
                        ${flag(m.home_iso)}
                        <span class="match__name">${escape(m.home || 'TBD')}</span>
                    </div>
                    <div>${live ? '<span class="badge-live">Live</span>' : ''}${score}</div>
                    <div class="match__team match__team--away">
                        <span class="match__name">${escape(m.away || 'TBD')}</span>
                        ${flag(m.away_iso)}
                    </div>
                </div>
                <div class="match__meta">
                    <span>${escape(m.venue || '')}</span>
                    <span>${escape(m.status || '')}</span>
                </div>
            </article>`;
    };

    const renderBanner = (panelId, gridId, items, headingFallback) => {
        const panel = $(panelId), grid = $(gridId);
        if (!panel || !grid) return;
        if (!items || !items.length) {
            panel.hidden = true;
            return;
        }
        panel.hidden = false;
        grid.innerHTML = items.map(matchCard).join('');
    };

    // ---------------- Polling ----------------

    let inFlight = false;
    const poll = async () => {
        if (inFlight) return;
        inFlight = true;
        try {
            const r = await fetch(window.SNAPSHOT_URL,
                { headers: { 'Accept': 'application/json' }});
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const data = await r.json();

            updateStandings(data.groups);
            updateBracket(data.bracket);
            renderBanner('#live-panel',   '#live-grid',   data.live);
            renderBanner('#recent-panel', '#recent-grid', data.recent);

            setStatus('ok', 'live');
            lastUpdated.textContent = ' · updated ' + new Date().toLocaleTimeString();
        } catch (e) {
            setStatus('err', 'connection issue');
            lastUpdated.textContent = ' · retrying…';
            console.error(e);
        } finally {
            inFlight = false;
        }
    };

    poll();
    setInterval(poll, POLL_MS);
})();
