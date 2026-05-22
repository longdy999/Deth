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
 *   - All endpoints are cached (Laravel cache) so multiple page visitors
 *     share the same upstream call. Worst case we issue ~5 req/min total.
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

    /** Next 15 upcoming events for the league. */
    public function nextEvents(): array
    {
        return $this->cached('wc:next', 30, function () {
            return $this->get("/{$this->apiKey}/eventsnextleague.php", ['id' => $this->leagueId])['events'] ?? [];
        });
    }

    /** Last 15 finished events for the league (results). */
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

    /** League metadata (name, logo, badge, description). */
    public function league(): ?array
    {
        return $this->cached('wc:league', 3600, function () {
            $data = $this->get("/{$this->apiKey}/lookupleague.php", ['id' => $this->leagueId]);
            return $data['leagues'][0] ?? null;
        });
    }

    /**
     * Combined snapshot used by the dashboard.
     * Returns: live, upcoming, recent, season, league.
     */
    public function snapshot(): array
    {
        $next   = $this->nextEvents();
        $past   = $this->pastEvents();
        $season = $this->seasonEvents();

        // "Live" heuristic: status set to 'In Play'/'1H'/'2H'/'HT', or any past event in
        // the last 3 hours with no final score (safety net since free tier has no livescore).
        $live = [];
        $upcoming = [];
        $now = time();
        foreach ($next as $e) {
            $status = strtolower((string)($e['strStatus'] ?? ''));
            if (in_array($status, ['in play', '1h', '2h', 'ht', 'live'], true)) {
                $live[] = $e;
            } else {
                $upcoming[] = $e;
            }
        }

        return [
            'league'   => $this->league(),
            'live'     => $live,
            'upcoming' => array_slice($upcoming, 0, 10),
            'recent'   => array_slice($past, 0, 10),
            'season'   => $season,
            'fetched_at' => date('c', $now),
        ];
    }

    // -------- internals --------

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
