<?php

namespace App\Http\Controllers;

use App\Services\SportsDbClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorldCupController extends Controller
{
    public function __construct(private SportsDbClient $sports) {}

    /** Render the dashboard shell. The data is loaded by the JS via /api/snapshot. */
    public function index(): View
    {
        return view('worldcup.index', [
            'league' => $this->sports->league(),
        ]);
    }

    /** Live snapshot consumed by the front-end every few seconds. */
    public function snapshot(): JsonResponse
    {
        $snap = $this->sports->snapshot();
        $season = $snap['season'] ?? [];

        // Shape the match cards (so JS gets uniform keys).
        $snap['live']     = array_map(fn($e) => $this->shapeMatch($e), $snap['live'] ?? []);
        $snap['upcoming'] = array_map(fn($e) => $this->shapeMatch($e), $snap['upcoming'] ?? []);
        $snap['recent']   = array_map(fn($e) => $this->shapeMatch($e), $snap['recent'] ?? []);

        $snap['bracket']   = $this->buildBracket($season);
        $snap['groups']    = $this->buildGroups($season);
        // Always-useful fallback while TheSportsDB hasn't labeled groups yet.
        $snap['matchdays'] = $this->buildMatchdays($season);

        // Drop the heavy raw season array to keep the response small.
        unset($snap['season']);
        return response()->json($snap);
    }

    // ---------- Derivations from event list ----------

    /**
     * Group season events into bracket rounds.
     *
     * TheSportsDB sets `intRound` for events. Soccer cups commonly use:
     *  125=Final, 150=Semi, 200=Quarter, 250=R16, 350=R32, 500=Group stage.
     * If `intRound` is missing we fall back to `strRound`.
     */
    private function buildBracket(array $events): array
    {
        $rounds = [
            'r32'   => ['title' => 'Round of 32',   'matches' => []],
            'r16'   => ['title' => 'Round of 16',   'matches' => []],
            'qf'    => ['title' => 'Quarter-finals','matches' => []],
            'sf'    => ['title' => 'Semi-finals',   'matches' => []],
            'third' => ['title' => 'Third Place',   'matches' => []],
            'final' => ['title' => 'Final',         'matches' => []],
        ];

        foreach ($events as $e) {
            $key = $this->classifyRound($e);
            if ($key === null) continue;
            $rounds[$key]['matches'][] = $this->shapeMatch($e);
        }

        // Sort each round chronologically.
        foreach ($rounds as &$r) {
            usort($r['matches'], fn($a, $b) => strcmp($a['kickoff'] ?? '', $b['kickoff'] ?? ''));
        }
        return array_values($rounds);
    }

    /** Group-stage standings derived from finished events with scores. */
    private function buildGroups(array $events): array
    {
        $groups = [];
        foreach ($events as $e) {
            $round = strtolower((string)($e['strRound'] ?? ''));
            // Only build standings when TheSportsDB tags the round with "Group X".
            if (!preg_match('/group\s+([a-l])/i', $round, $m)) continue;
            $groupKey = strtoupper($m[1]);
            $groups[$groupKey] ??= [];

            $home = $e['strHomeTeam'] ?? null;
            $away = $e['strAwayTeam'] ?? null;
            $hs   = $e['intHomeScore'] ?? null;
            $as   = $e['intAwayScore'] ?? null;

            foreach ([$home, $away] as $t) {
                if (!$t) continue;
                $groups[$groupKey][$t] ??= [
                    'team' => $t, 'mp' => 0, 'w' => 0, 'd' => 0, 'l' => 0,
                    'gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0, 'badge' => null,
                ];
            }
            if ($home && !empty($e['strHomeTeamBadge'])) $groups[$groupKey][$home]['badge'] = $e['strHomeTeamBadge'];
            if ($away && !empty($e['strAwayTeamBadge'])) $groups[$groupKey][$away]['badge'] = $e['strAwayTeamBadge'];

            if ($home && $away && $hs !== null && $as !== null && $hs !== '' && $as !== '') {
                $hs = (int)$hs; $as = (int)$as;
                $groups[$groupKey][$home]['mp']++;
                $groups[$groupKey][$away]['mp']++;
                $groups[$groupKey][$home]['gf'] += $hs;
                $groups[$groupKey][$home]['ga'] += $as;
                $groups[$groupKey][$away]['gf'] += $as;
                $groups[$groupKey][$away]['ga'] += $hs;
                if     ($hs > $as) { $groups[$groupKey][$home]['w']++; $groups[$groupKey][$home]['pts'] += 3; $groups[$groupKey][$away]['l']++; }
                elseif ($hs < $as) { $groups[$groupKey][$away]['w']++; $groups[$groupKey][$away]['pts'] += 3; $groups[$groupKey][$home]['l']++; }
                else               { $groups[$groupKey][$home]['d']++; $groups[$groupKey][$away]['d']++;
                                     $groups[$groupKey][$home]['pts']++; $groups[$groupKey][$away]['pts']++; }
            }
        }

        $out = [];
        ksort($groups);
        foreach ($groups as $k => $teams) {
            foreach ($teams as &$t) { $t['gd'] = $t['gf'] - $t['ga']; }
            usort($teams, fn($a, $b) => [$b['pts'], $b['gd'], $b['gf']] <=> [$a['pts'], $a['gd'], $a['gf']]);
            $out[] = ['name' => "Group $k", 'table' => array_values($teams)];
        }
        return $out;
    }

    /**
     * Fallback view of the group stage: bucket every non-knockout event by matchday
     * (TheSportsDB's `intRound`). Useful before group letters are populated.
     */
    private function buildMatchdays(array $events): array
    {
        $buckets = [];
        foreach ($events as $e) {
            if ($this->classifyRound($e) !== null) continue; // skip knockouts
            $md = (int)($e['intRound'] ?? 0);
            if ($md < 1 || $md > 50) continue; // ignore unknown
            $buckets[$md] ??= [];
            $buckets[$md][] = $this->shapeMatch($e);
        }
        ksort($buckets);
        $out = [];
        foreach ($buckets as $md => $matches) {
            usort($matches, fn($a, $b) => strcmp($a['kickoff'] ?? '', $b['kickoff'] ?? ''));
            $out[] = ['name' => "Matchday $md", 'matches' => $matches];
        }
        return $out;
    }

    private function classifyRound(array $e): ?string
    {
        $name = strtolower((string)($e['strRound'] ?? ''));
        $int  = (int)($e['intRound'] ?? 0);

        if ($int === 125 || str_contains($name, 'final') && !str_contains($name, 'semi') && !str_contains($name, 'quarter') && !str_contains($name, 'third')) return 'final';
        if ($int === 150 || str_contains($name, 'semi'))                    return 'sf';
        if ($int === 200 || str_contains($name, 'quarter'))                 return 'qf';
        if ($int === 250 || str_contains($name, 'round of 16'))             return 'r16';
        if ($int === 350 || str_contains($name, 'round of 32'))             return 'r32';
        if (str_contains($name, 'third') || str_contains($name, '3rd'))     return 'third';
        return null; // group stage handled separately
    }

    private function shapeMatch(array $e): array
    {
        return [
            'id'         => $e['idEvent'] ?? null,
            'home'       => $e['strHomeTeam'] ?? 'TBD',
            'away'       => $e['strAwayTeam'] ?? 'TBD',
            'home_badge' => $e['strHomeTeamBadge'] ?? null,
            'away_badge' => $e['strAwayTeamBadge'] ?? null,
            'home_score' => $this->intOrNull($e['intHomeScore'] ?? null),
            'away_score' => $this->intOrNull($e['intAwayScore'] ?? null),
            'kickoff'    => trim(($e['dateEvent'] ?? '') . ' ' . ($e['strTime'] ?? '')),
            'venue'      => $e['strVenue'] ?? null,
            'status'     => $e['strStatus'] ?? null,
        ];
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int)$v;
    }
}
