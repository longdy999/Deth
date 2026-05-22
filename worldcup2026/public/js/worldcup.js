/* Live polling + render for the World Cup 2026 dashboard.
 * Polls our own backend (NOT TheSportsDB), so we never hit the upstream rate limit. */
(() => {
    const POLL_MS = 10_000; // 10s — backend cache absorbs the rest

    const $ = (id) => document.getElementById(id);
    const dot = $('status-dot');
    const statusText = $('status-text');
    const lastUpdated = $('last-updated');

    const setStatus = (state, text) => {
        dot.classList.remove('ok', 'err');
        if (state === 'ok')  dot.classList.add('ok');
        if (state === 'err') dot.classList.add('err');
        statusText.textContent = text;
    };

    const escape = (s) => String(s ?? '').replace(/[&<>"']/g, (c) =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const teamHtml = (name, badge) => `
        <div class="team">
            ${badge ? `<img src="${escape(badge)}" alt="">` : ''}
            <span class="team__name">${escape(name || 'TBD')}</span>
        </div>`;

    const isLive = (m) => {
        const s = (m.status || '').toLowerCase();
        return ['in play','1h','2h','ht','live'].includes(s);
    };

    const matchCard = (m, opts = {}) => {
        const hs = m.home_score, as = m.away_score;
        const hasScore = hs !== null && hs !== undefined && as !== null && as !== undefined;
        const live = isLive(m);
        const scoreHtml = hasScore
            ? `<span class="score">${hs}</span><span class="score score--vs">–</span><span class="score">${as}</span>`
            : `<span class="score score--vs">vs</span>`;
        const when = m.kickoff
            ? new Date(m.kickoff.replace(' ', 'T') + (m.kickoff.length <= 10 ? '' : 'Z'))
            : null;
        const whenText = when && !isNaN(+when)
            ? when.toLocaleString(undefined, { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' })
            : (m.kickoff || '');
        return `
        <article class="match">
            <div class="match__teams">
                ${teamHtml(m.home, m.home_badge)}
                <div>${live ? '<span class="badge-live">Live</span>' : ''}${scoreHtml}</div>
                ${teamHtml(m.away, m.away_badge)}
            </div>
            <div class="match__meta">
                <span>${escape(whenText)}</span>
                <span>${escape(m.venue || '')}</span>
            </div>
        </article>`;
    };

    const renderMatches = (containerId, matches, emptyText) => {
        const el = $(containerId);
        if (!matches || !matches.length) {
            el.innerHTML = `<div class="empty">${escape(emptyText)}</div>`;
            return;
        }
        el.innerHTML = matches.map(m => matchCard(m)).join('');
    };

    const renderLive = (live, upcoming) => {
        const list = [...(live || []), ...(upcoming || [])];
        renderMatches('live-grid', list, 'No live or upcoming matches yet.');
    };

    const renderBracket = (rounds) => {
        const el = $('bracket');
        const nonEmpty = (rounds || []).filter(r => r.matches && r.matches.length);
        if (!nonEmpty.length) {
            el.innerHTML = `<div class="empty">Bracket will populate after the group stage.</div>`;
            return;
        }
        el.innerHTML = nonEmpty.map(r => `
            <div class="round">
                <h3>${escape(r.title)}</h3>
                ${r.matches.map(m => matchCard(m)).join('')}
            </div>
        `).join('');
    };

    const renderGroups = (groups, matchdays) => {
        const el = $('groups');
        const title = $('groups-title');
        // Prefer real standings; otherwise show schedule by matchday.
        if (groups && groups.length) {
            title.textContent = 'Group Stage Standings';
            el.classList.add('groups');
            el.classList.remove('matchdays');
            el.innerHTML = groups.map(g => `
                <div class="group">
                    <h3>${escape(g.name)}</h3>
                    <table>
                        <thead>
                            <tr><th>Team</th><th>MP</th><th>W</th><th>D</th><th>L</th><th>GD</th><th>Pts</th></tr>
                        </thead>
                        <tbody>
                            ${g.table.map(t => `
                                <tr>
                                    <td>${escape(t.team)}</td>
                                    <td>${t.mp}</td><td>${t.w}</td><td>${t.d}</td><td>${t.l}</td>
                                    <td>${t.gd > 0 ? '+'+t.gd : t.gd}</td>
                                    <td class="pts">${t.pts}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                </div>`).join('');
            return;
        }
        if (matchdays && matchdays.length) {
            title.textContent = 'Group Stage — Schedule';
            el.classList.remove('groups');
            el.classList.add('matchdays');
            el.innerHTML = matchdays.map(md => `
                <div class="matchday">
                    <h3>${escape(md.name)}</h3>
                    <div class="match-grid">${md.matches.map(m => matchCard(m)).join('')}</div>
                </div>`).join('');
            return;
        }
        el.innerHTML = `<div class="empty">Group stage data not yet published.</div>`;
    };

    let inFlight = false;
    const poll = async () => {
        if (inFlight) return;
        inFlight = true;
        try {
            const r = await fetch(window.SNAPSHOT_URL, { headers: { 'Accept': 'application/json' }});
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const data = await r.json();
            renderLive(data.live, data.upcoming);
            renderMatches('recent-grid', data.recent, 'No completed matches yet.');
            renderGroups(data.groups, data.matchdays);
            renderBracket(data.bracket);
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
