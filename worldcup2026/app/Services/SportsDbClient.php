<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around TheSportsDB free API.
 *
 * Rate-limit safety:
 *   - Free tier: 30 requests/min.
 *   - All endpoints are cached so multiple page visitors share the same
 *     upstream call. Worst case we issue ~7 req/min total.
 */
class SportsDbClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $leagueId;
    private string $season;

    public function __construct()
    {
        $this->apiKey   = (string) config('sportsdb.key', '3');
        $this->leagueId = (string) config('sportsdb.league_id', '4429'); // FIFA World Cup
        $this->season   = (string) config('sportsdb.season', '2026');
        $this->baseUrl  = rtrim((string) config('sportsdb.base_url', 'https://www.thesportsdb.com/api/v1/json'), '/');
    }

    /** Next upcoming events for the league. */
    public function nextEvents(): array
    {
        return $this->cached('wc:next', 30, function () {
            return $this->get("/{$this->apiKey}/eventsnextleague.php", ['id' => $this->leagueId])['events'] ?? [];
        });
    }

    /** Last finished events for the league. */
    public function pastEvents(): array
    {
        return $this->cached('wc:past', 60, function () {
            return $this->get("/{$this->apiKey}/eventspastleague.php", ['id' => $this->leagueId])['events'] ?? [];
        });
    }

    /** All scheduled events for the configured season. */
    public function seasonEvents(): array
    {
        return $this->cached('wc:season:' . $this->season, 300, function () {
            return $this->get("/{$this->apiKey}/eventsseason.php", [
                'id' => $this->leagueId,
                's'  => $this->season,
            ])['events'] ?? [];
        });
    }

    /** Events for a specific past season (e.g. '2022', '2018'). */
    public function eventsForSeason(string $season): array
    {
        return $this->cached("wc:season:$season", 6 * 3600, function () use ($season) {
            return $this->get("/{$this->apiKey}/eventsseason.php", [
                'id' => $this->leagueId,
                's'  => $season,
            ])['events'] ?? [];
        });
    }

    /** League metadata. */
    public function league(): ?array
    {
        return $this->cached('wc:league', 3600, function () {
            $data = $this->get("/{$this->apiKey}/lookupleague.php", ['id' => $this->leagueId]);
            return $data['leagues'][0] ?? null;
        });
    }

    /**
     * Combined snapshot used by the dashboard.
     * Returns: league, featured, live, upcoming, recent, season, fetched_at.
     *
     * Guarantees ≥3 items in `upcoming` and `recent` whenever possible by
     * padding from seasonEvents (future-dated, no score) and from previous
     * World Cup seasons (2022, 2018, 2014).
     */
    public function snapshot(): array
    {
        $next   = $this->nextEvents();
        $past   = $this->pastEvents();
        $season = $this->seasonEvents();
        $now    = time();

        // ---- LIVE ---------------------------------------------------------
        $live = [];
        $upcomingFromNext = [];
        foreach ($next as $e) {
            $status = strtolower((string)($e['strStatus'] ?? ''));
            if (in_array($status, ['in play', '1h', '2h', 'ht', 'live'], true)) {
                $live[] = $e;
            } else {
                $upcomingFromNext[] = $e;
            }
        }

        // ---- UPCOMING (chronological, dedup) ------------------------------
        $upcomingPool = $upcomingFromNext;
        foreach ($season as $e) {
            $kickoff = $this->kickoffTs($e);
            if ($kickoff !== null && $kickoff > $now) {
                $upcomingPool[] = $e;
            }
        }
        $upcoming = $this->dedupeAndSort($upcomingPool, asc: true);
        $upcoming = array_slice($upcoming, 0, 12);

        // ---- RECENT (need ≥3, pad from past WC seasons) -------------------
        $recentPool = [];
        foreach ($past as $e) $recentPool[] = $e;
        foreach ($season as $e) {
            $kickoff = $this->kickoffTs($e);
            $hs = $e['intHomeScore'] ?? null;
            $as = $e['intAwayScore'] ?? null;
            $hasScore = $hs !== null && $hs !== '' && $as !== null && $as !== '';
            if ($kickoff !== null && $kickoff < $now && $hasScore) {
                $recentPool[] = $e;
            }
        }
        $recentPool = $this->dedupeAndSort($recentPool, asc: false);

        if (count($recentPool) < 3) {
            foreach (['2022', '2018', '2014'] as $s) {
                if (count($recentPool) >= 6) break;
                $hist = $this->eventsForSeason($s);
                $hist = array_filter($hist, function ($e) {
                    $hs = $e['intHomeScore'] ?? null; $as = $e['intAwayScore'] ?? null;
                    return $hs !== null && $hs !== '' && $as !== null && $as !== '';
                });
                $recentPool = array_merge($recentPool, array_values($hist));
                $recentPool = $this->dedupeAndSort($recentPool, asc: false);
            }
        }
        $recent = array_slice($recentPool, 0, 6);

        // ---- FEATURED (live > next-upcoming) ------------------------------
        $featured = $live[0] ?? ($upcoming[0] ?? null);

        return [
            'league'     => $this->league(),
            'featured'   => $featured,
            'live'       => $live,
            'upcoming'   => $upcoming,
            'recent'     => $recent,
            'season'     => $season,
            'fetched_at' => date('c', $now),
        ];
    }

    // -------- internals --------

    private function kickoffTs(array $e): ?int
    {
        $date = $e['dateEvent'] ?? null;
        $time = $e['strTime'] ?? '00:00:00';
        if (!$date) return null;
        $ts = strtotime("$date $time UTC");
        return $ts ?: null;
    }

    private function dedupeAndSort(array $events, bool $asc = true): array
    {
        $byId = [];
        foreach ($events as $e) {
            $id = $e['idEvent'] ?? null;
            if ($id === null) {
                $byId[spl_object_hash((object)$e)] = $e;
            } else {
                $byId[$id] = $e;
            }
        }
        $events = array_values($byId);
        usort($events, function ($a, $b) use ($asc) {
            $ta = $this->kickoffTs($a) ?? 0;
            $tb = $this->kickoffTs($b) ?? 0;
            return $asc ? ($ta <=> $tb) : ($tb <=> $ta);
        });
        return $events;
    }

    private function cached(string $key, int $ttl, \Closure $fetcher): mixed
    {
        return Cache::remember($key, $ttl, $fetcher);
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $resp = Http::timeout(8)
                ->retry(2, 200)
                ->acceptJson()
                ->get($this->baseUrl . $path, $query);

            if ($resp->status() === 429) {
                Log::warning('TheSportsDB rate-limited', ['path' => $path]);
                return [];
            }
            if (!$resp->successful()) {
                Log::warning('TheSportsDB non-2xx', ['path' => $path, 'status' => $resp->status()]);
                return [];
            }
            return $resp->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('TheSportsDB request failed', ['path' => $path, 'err' => $e->getMessage()]);
            return [];
        }
    }
}
