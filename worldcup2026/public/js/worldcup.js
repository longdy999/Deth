/* ===========================================================================
 * FIFA World Cup 26 — Match Center
 *
 * Two-layer architecture:
 *   1. Server pre-renders the full skeleton (12 group tables + 32-match
 *      bracket with FIFA slot codes).
 *   2. JS polls /api/snapshot every 10 s and patches in:
 *        - hero featured match,
 *        - up-next + recent results sections,
 *        - standings stats & re-ordering,
 *        - bracket cells (resolves slot codes into real teams).
 *
 * Also handles the dark/light theme toggle (persisted in localStorage).
 * =========================================================================== */
(() => {
    const POLL_MS = 10_000;
    const $  = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    // ===================== Theme toggle =====================================
    const themeToggle = $('#theme-toggle');
    const setTheme = (mode) => {
        document.documentElement.dataset.theme = mode;
        try { localStorage.setItem('wc-theme', mode); } catch (e) {}
    };
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const cur = document.documentElement.dataset.theme || 'light';
            setTheme(cur === 'dark' ? 'light' : 'dark');
        });
    }
    // Update colour-scheme of the document for native form controls
    const syncColorScheme = () => {
        document.documentElement.style.colorScheme = document.documentElement.dataset.theme;
    };
    syncColorScheme();
    new MutationObserver(syncColorScheme).observe(document.documentElement,
        { attributes: true, attributeFilter: ['data-theme'] });

    // ===================== Status & helpers =================================
    const dot         = $('#status-dot');
    const statusText  = $('#status-text');
    const lastUpdated = $('#last-updated');

    const setStatus = (state, text) => {
        if (!dot) return;
        dot.classList.remove('ok', 'err');
        if (state === 'ok')  dot.classList.add('ok');
        if (state === 'err') dot.classList.add('err');
        if (statusText) statusText.textContent = text;
    };

    const escape = (s) => String(s ?? '').replace(/[&<>"']/g, (c) =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const flagUrl = (iso, size = 'w40') =>
        iso ? `https://flagcdn.com/${size}/${iso.toLowerCase()}.png` : null;

    const parseKickoff = (s) => {
        if (!s) return null;
        const d = new Date(s.replace(' ', 'T'));
        return isNaN(+d) ? null : d;
    };
    const fmtKickoff = (s, opts) => {
        const d = parseKickoff(s);
        if (!d) return s || '';
        return d.toLocaleString(undefined, opts || {
            weekday: 'short', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    };
    const fmtDate = (s) => fmtKickoff(s,
        { weekday: 'short', month: 'short', day: 'numeric' });
    const fmtTime = (s) => fmtKickoff(s, { hour: '2-digit', minute: '2-digit' });

    const findRow = (team) => {
        if (!team) return null;
        const escaped = team.replace(/"/g, '\\"');
        return document.querySelector(`.row[data-team="${escaped}"]`);
    };

    const isLive     = (m) => (m && m.status) === 'Live';
    const isFinished = (m) => (m && m.status) === 'Finished';

    // ===================== Hero featured match ==============================
    const renderHero = (m) => {
        const card = $('#featured-card');
        if (!card) return;
        if (!m) {
            card.innerHTML = '<div style="opacity:.7;text-align:center">No featured match.</div>';
            return;
        }
        const live     = isLive(m);
        const finished = isFinished(m);
        const hasScore = m.home_score !== null && m.home_score !== undefined
                      && m.away_score !== null && m.away_score !== undefined;

        const flag = (iso) => iso
            ? `<span class="hero__flag"><img src="${flagUrl(iso, 'w160')}" alt=""></span>`
            : `<span class="hero__flag"></span>`;

        const center = hasScore
            ? `<div class="hero__score">${m.home_score} – ${m.away_score}</div>
               <span class="hero__status ${live ? 'hero__status--live' : ''}">${live ? 'Live' : 'Full Time'}</span>`
            : `<div class="hero__kickoff">${escape(fmtDate(m.kickoff))}<br>${escape(fmtTime(m.kickoff))}</div>
               <span class="hero__status">Upcoming</span>`;

        card.innerHTML = `
            <div class="hero__team hero__team--home">
                ${flag(m.home_iso)}
                <span class="hero__name">${escape(m.home || 'TBD')}</span>
            </div>
            <div class="hero__center">${center}</div>
            <div class="hero__team hero__team--away">
                <span class="hero__name">${escape(m.away || 'TBD')}</span>
                ${flag(m.away_iso)}
            </div>
            <div class="hero__meta">
                <span>${escape(m.venue || '—')}</span>
                <span>${escape(m.round || '')}</span>
            </div>
        `;
    };

    // ===================== Match card (Up Next / Recent) ====================
    const matchCard = (m) => {
        const live     = isLive(m);
        const finished = isFinished(m);
        const hasScore = m.home_score !== null && m.home_score !== undefined
                      && m.away_score !== null && m.away_score !== undefined;

        const chip = live
            ? '<span class="chip chip--live">● Live</span>'
            : finished
                ? '<span class="chip chip--finished">Finished</span>'
                : '<span class="chip chip--upcoming">Upcoming</span>';

        const flag = (iso) => iso
            ? `<img src="${flagUrl(iso, 'w40')}" alt="">` : '';

        const center = hasScore
            ? `<div class="match__score">${m.home_score} – ${m.away_score}</div>
               <div class="match__kickoff">${escape(fmtDate(m.kickoff))}</div>`
            : `<div class="match__kickoff">${escape(fmtDate(m.kickoff))}</div>
               <div class="match__kickoff">${escape(fmtTime(m.kickoff))}</div>`;

        return `
            <article class="match">
                <div class="match__top">
                    <span class="match__round">${escape(m.round || (finished ? 'Result' : 'Fixture'))}</span>
                    ${chip}
                </div>
                <div class="match__teams">
                    <div class="match__team match__team--home">
                        ${flag(m.home_iso)}
                        <span class="match__name">${escape(m.home || 'TBD')}</span>
                    </div>
                    <div class="match__center">${center}</div>
                    <div class="match__team match__team--away">
                        <span class="match__name">${escape(m.away || 'TBD')}</span>
                        ${flag(m.away_iso)}
                    </div>
                </div>
                <div class="match__meta">
                    <span>${escape(m.venue || '—')}</span>
                    <span>${m.kickoff ? escape(fmtTime(m.kickoff)) : ''}</span>
                </div>
            </article>`;
    };

    const renderGrid = (gridSel, items, minCount = 3) => {
        const grid = $(gridSel);
        if (!grid) return;
        if (!items || !items.length) {
            grid.innerHTML = `
                <div class="match" style="grid-column:1/-1;text-align:center;color:var(--muted);">
                    No matches to show yet.
                </div>`;
            return;
        }
        grid.innerHTML = items.slice(0, Math.max(minCount, 3)).map(matchCard).join('');
    };

    const setCount = (sel, items, label) => {
        const el = $(sel);
        if (!el) return;
        const n = (items && items.length) || 0;
        el.textContent = n ? `${n} ${label}` : '';
    };

    // ===================== Standings overlay ================================
    const updateStandings = (groups) => {
        if (!Array.isArray(groups)) return;
        for (const g of groups) {
            const groupEl = document.querySelector(`.group[data-group="${g.letter}"]`);
            if (!groupEl) continue;
            const tbody = groupEl.querySelector('.group__table tbody');
            if (!tbody) continue;

            (g.table || []).forEach((t, idx) => {
                const row = findRow(t.team);
                if (!row) return;
                row.dataset.rank = String(idx + 1);
                row.querySelector('.rank').textContent = String(idx + 1);

                const setStat = (k, v) => {
                    const el = row.querySelector(`[data-stat="${k}"]`);
                    if (!el) return;
                    el.textContent = (k === 'gd' && v > 0) ? `+${v}` : String(v ?? 0);
                };
                setStat('mp',  t.mp);
                setStat('w',   t.w);
                setStat('d',   t.d);
                setStat('l',   t.l);
                setStat('gf',  t.gf);
                setStat('ga',  t.ga);
                setStat('gd',  t.gd);
                setStat('pts', t.pts);

                const form = row.querySelector('[data-form]');
                if (form) {
                    const last5 = (t.form || []).slice(-5);
                    const cells = [];
                    for (let i = 0; i < 5; i++) {
                        cells.push(last5[i]
                            ? `<span class="dot-form is-${last5[i].toLowerCase()}"></span>`
                            : `<span class="dot-form">–</span>`);
                    }
                    form.innerHTML = cells.join('');
                }
                tbody.appendChild(row);   // re-order to sorted position
            });
        }
    };

    // ===================== Bracket overlay ==================================
    const updateBracket = (rounds) => {
        if (!Array.isArray(rounds)) return;
        for (const r of rounds) {
            for (const m of (r.matches || [])) {
                // Each match-id renders twice (desktop tree + mobile strip)
                const cells = document.querySelectorAll(`.brk[data-mid="${m.id}"]`);
                cells.forEach((cell) => {
                    const sides = {
                        home: cell.querySelector('.brk__team[data-side="home"]'),
                        away: cell.querySelector('.brk__team[data-side="away"]'),
                    };
                    const apply = (side, name, iso, score) => {
                        const el = sides[side]; if (!el) return;
                        const nameEl  = el.querySelector('[data-name]');
                        const flagEl  = el.querySelector('[data-flag]');
                        const scoreEl = el.querySelector('[data-score]');
                        if (name) {
                            nameEl.textContent = name;
                            nameEl.classList.remove('is-placeholder');
                        } else {
                            nameEl.classList.add('is-placeholder');
                        }
                        flagEl.innerHTML = iso
                            ? `<img src="${flagUrl(iso, 'w40')}" alt="">` : '';
                        scoreEl.textContent =
                            (score === null || score === undefined) ? '–' : String(score);
                    };
                    apply('home', m.home_team, m.home_iso, m.home_score);
                    apply('away', m.away_team, m.away_iso, m.away_score);

                    const hs = m.home_score, as = m.away_score;
                    sides.home.classList.toggle('is-winner', hs !== null && as !== null && hs > as);
                    sides.away.classList.toggle('is-winner', hs !== null && as !== null && as > hs);
                });
            }
        }
    };

    // ===================== Polling ==========================================
    let inFlight = false;
    const poll = async () => {
        if (inFlight) return;
        inFlight = true;
        try {
            const r = await fetch(window.SNAPSHOT_URL,
                { headers: { 'Accept': 'application/json' }});
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const data = await r.json();

            renderHero(data.featured);
            renderGrid('#upcoming-grid', data.upcoming, 3);
            renderGrid('#recent-grid',   data.recent,   3);
            setCount('#upcoming-count',  data.upcoming, 'matches scheduled');
            setCount('#recent-count',    data.recent,   'recent matches');
            updateStandings(data.groups);
            updateBracket(data.bracket);

            setStatus('ok', 'live');
            if (lastUpdated)
                lastUpdated.textContent = 'Updated ' + new Date().toLocaleTimeString();
        } catch (e) {
            setStatus('err', 'connection issue');
            if (lastUpdated) lastUpdated.textContent = 'Retrying…';
            console.error(e);
        } finally {
            inFlight = false;
        }
    };

    poll();
    setInterval(poll, POLL_MS);
})();
