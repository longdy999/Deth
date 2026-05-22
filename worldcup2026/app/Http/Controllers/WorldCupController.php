<?php

namespace App\Http\Controllers;

use App\Services\SportsDbClient;
use App\Support\WorldCupDraw;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WorldCupController extends Controller
{
    public function __construct(private SportsDbClient $sports) {}

    /**
     * Render the dashboard shell. The Blade template pre-renders the static
     * draw (12 groups × 4 teams + full bracket scaffold) so the page is
     * useful even before the JS overlay loads. JS then tops it up with
     * live scores from /api/snapshot.
     */
    public function index(): View
    {
        return view('worldcup.index', [
            'page'    => 'home',
            'league'  => $this->sports->league(),
            'groups'  => WorldCupDraw::groups(),
            'bracket' => WorldCupDraw::bracket(),
        ]);
    }

    /** Dedicated Group Stage page. */
    public function groups(): View
    {
        return view('worldcup.groups', [
            'page'         => 'groups',
            'league'       => $this->sports->league(),
            'groups'       => WorldCupDraw::groups(),
            'bracket'      => WorldCupDraw::bracket(),
            'groupMatches' => $this->groupMatchesByLetter(),
        ]);
    }

    /** Dedicated Knockout Bracket page. */
    public function bracket(): View
    {
        return view('worldcup.bracket', [
            'page'    => 'bracket',
            'league'  => $this->sports->league(),
            'groups'  => WorldCupDraw::groups(),
            'bracket' => WorldCupDraw::bracket(),
        ]);
    }

    /** JSON snapshot consumed by the front-end every 10 s. */
    public function snapshot(): JsonResponse
    {
        $snap   = $this->sports->snapshot();
        $season = $snap['season'] ?? [];

        // Shape the match cards (uniform keys for the JS).
        $snap['live']     = array_map(fn($e) => $this->shapeMatch($e, 'live'),     $snap['live'] ?? []);
        $snap['upcoming'] = array_map(fn($e) => $this->shapeMatch($e, 'upcoming'), $snap['upcoming'] ?? []);
        $snap['recent']   = array_map(fn($e) => $this->shapeMatch($e, 'recent'),   $snap['recent'] ?? []);
        if (!empty($snap['featured'])) {
            // Decide bucket by checking the source live array (raw, not yet shaped).
            $rawLive  = $this->sports->snapshot()['live'] ?? [];
            $isLiveFt = false;
            $ftId     = $snap['featured']['idEvent'] ?? null;
            foreach ($rawLive as $rl) {
                if (($rl['idEvent'] ?? null) === $ftId) { $isLiveFt = true; break; }
            }
            $snap['featured'] = $this->shapeMatch($snap['featured'], $isLiveFt ? 'live' : 'upcoming');
        }

        // Standings: start from the static draw, then overlay played-match results.
        $snap['groups']  = $this->buildGroupStandings($season);

        // Bracket: start from the static scaffold, then resolve any matches
        // that the API has actually played (look up by team-pair).
        $snap['bracket'] = $this->buildBracketWithResults($season);

        unset($snap['season']);
        return response()->json($snap);
    }

    // ---------- Standings ----------

    /**
     * Build 12 group tables. Every team starts at 0/0/0 (so the page never
     * looks empty), and any played event whose teams both belong to the
     * same group gets folded into the points table.
     */
    private function buildGroupStandings(array $events): array
    {
        $draw = WorldCupDraw::groups();

        // Initialize empty tables.
        $teamGroup = []; // normalized team name -> group letter
        $tables    = []; // group letter -> [team -> stats]
        foreach ($draw as $letter => $teams) {
            $tables[$letter] = [];
            foreach ($teams as $t) {
                $tables[$letter][$t['team']] = [
                    'team' => $t['team'],
                    'iso'  => $t['iso'],
                    'mp'   => 0, 'w' => 0, 'd' => 0, 'l' => 0,
                    'gf'   => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0,
                    'form' => [], // last 5 results: 'W' | 'D' | 'L'
                ];
                $teamGroup[$this->normalize($t['team'])] = $letter;
            }
        }

        // TheSportsDB sometimes returns slightly different spellings. Map them.
        $apiAliases = [
            'south korea'        => 'Korea Republic',
            'czech republic'     => 'Czechia',
            'bosnia-herzegovina' => 'Bosnia and Herzegovina',
            'ivory coast'        => "Côte d'Ivoire",
            'iran'               => 'IR Iran',
            'cape verde'         => 'Cabo Verde',
            'turkey'             => 'Türkiye',
            'dr congo'           => 'Congo DR',
            'curacao'            => 'Curaçao',
        ];
        $resolveTeam = function (?string $name) use ($teamGroup, $apiAliases): ?string {
            if (!$name) return null;
            $norm = $this->normalize($name);
            $canonical = $apiAliases[$norm] ?? $name;
            $lookup = $this->normalize($canonical);
            return isset($teamGroup[$lookup]) ? $canonical : null;
        };

        // Sort events chronologically so 'form' is in the right order.
        usort($events, fn($a, $b) => strcmp(
            ($a['dateEvent'] ?? '') . ($a['strTime'] ?? ''),
            ($b['dateEvent'] ?? '') . ($b['strTime'] ?? '')
        ));

        foreach ($events as $e) {
            $home = $resolveTeam($e['strHomeTeam'] ?? null);
            $away = $resolveTeam($e['strAwayTeam'] ?? null);
            if (!$home || !$away) continue;

            $hs = $e['intHomeScore'] ?? null;
            $as = $e['intAwayScore'] ?? null;
            if ($hs === null || $hs === '' || $as === null || $as === '') continue;

            $g = $teamGroup[$this->normalize($home)] ?? null;
            // Only count matches where both teams are in the SAME group.
            if (!$g || ($teamGroup[$this->normalize($away)] ?? null) !== $g) continue;

            $hs = (int)$hs; $as = (int)$as;
            $h =& $tables[$g][$home];
            $a =& $tables[$g][$away];

            $h['mp']++; $a['mp']++;
            $h['gf'] += $hs; $h['ga'] += $as;
            $a['gf'] += $as; $a['ga'] += $hs;

            if ($hs > $as) {
                $h['w']++; $h['pts'] += 3; $a['l']++;
                $h['form'][] = 'W'; $a['form'][] = 'L';
            } elseif ($hs < $as) {
                $a['w']++; $a['pts'] += 3; $h['l']++;
                $a['form'][] = 'W'; $h['form'][] = 'L';
            } else {
                $h['d']++; $a['d']++; $h['pts']++; $a['pts']++;
                $h['form'][] = 'D'; $a['form'][] = 'D';
            }
            unset($h, $a);
        }

        // Finalize: GD, last-5 form, sort each group.
        $out = [];
        foreach ($tables as $letter => $teams) {
            foreach ($teams as &$t) {
                $t['gd']   = $t['gf'] - $t['ga'];
                $t['form'] = array_slice($t['form'], -5);
            }
            usort($teams, fn($x, $y) =>
                [$y['pts'], $y['gd'], $y['gf']] <=> [$x['pts'], $x['gd'], $x['gf']]);
            $out[] = ['name' => "Group $letter", 'letter' => $letter, 'table' => array_values($teams)];
        }
        return $out;
    }

    // ---------- Bracket ----------

    /**
     * Return the static FIFA bracket, overlayed with any live results.
     * For each scaffold match we look for a TheSportsDB event whose
     * round string contains the FIFA M-number, OR whose team pair has
     * been resolved from a feeder slot (e.g. "1E" → Group E winner).
     *
     * The slot resolution is best-effort: when the static slot still
     * holds a placeholder (e.g. "W74"), we leave it as-is so the UI
     * displays the FIFA code.
     */
    private function buildBracketWithResults(array $events): array
    {
        $bracket = WorldCupDraw::bracket();

        // Index API events by FIFA M-number when present in strRound.
        $byMid = [];
        foreach ($events as $e) {
            $round = (string)($e['strRound'] ?? '');
            if (preg_match('/\bM\d+\b/i', $round, $m)) {
                $byMid[strtoupper($m[0])] = $e;
            }
        }

        $rounds = [
            'r32'   => 'Round of 32',
            'r16'   => 'Round of 16',
            'qf'    => 'Quarter-finals',
            'sf'    => 'Semi-finals',
            'third' => 'Third Place',
            'final' => 'Final',
        ];

        $out = [];
        foreach ($rounds as $key => $title) {
            $matches = [];
            foreach ($bracket[$key] ?? [] as $m) {
                $live = $byMid[$m['id']] ?? null;
                $matches[] = [
                    'id'         => $m['id'],
                    'kickoff'    => $m['date'],
                    'home_label' => $m['home'],   // FIFA slot code (1E, W74, …)
                    'away_label' => $m['away'],
                    'home_team'  => $live['strHomeTeam']  ?? null,
                    'away_team'  => $live['strAwayTeam']  ?? null,
                    'home_iso'   => $live ? WorldCupDraw::isoFor((string)($live['strHomeTeam'] ?? '')) : null,
                    'away_iso'   => $live ? WorldCupDraw::isoFor((string)($live['strAwayTeam'] ?? '')) : null,
                    'home_score' => $this->intOrNull($live['intHomeScore'] ?? null),
                    'away_score' => $this->intOrNull($live['intAwayScore'] ?? null),
                    'venue'      => $live['strVenue']     ?? null,
                    'status'     => $live['strStatus']    ?? null,
                ];
            }
            $out[] = ['key' => $key, 'title' => $title, 'matches' => $matches];
        }
        return $out;
    }

    // ---------- Shared helpers ----------

    private function shapeMatch(array $e, ?string $bucket = null): array
    {
        $hs        = $this->intOrNull($e['intHomeScore'] ?? null);
        $as        = $this->intOrNull($e['intAwayScore'] ?? null);
        $rawStatus = strtolower((string)($e['strStatus'] ?? ''));

        // Friendly status: Finished | Live | Upcoming
        if (in_array($rawStatus, ['in play', '1h', '2h', 'ht', 'live'], true) || $bucket === 'live') {
            $status = 'Live';
        } elseif ($hs !== null && $as !== null) {
            $status = 'Finished';
        } elseif ($bucket === 'recent') {
            $status = 'Finished';
        } else {
            $status = 'Upcoming';
        }

        return [
            'id'         => $e['idEvent'] ?? null,
            'home'       => $e['strHomeTeam'] ?? 'TBD',
            'away'       => $e['strAwayTeam'] ?? 'TBD',
            'home_iso'   => WorldCupDraw::isoFor((string)($e['strHomeTeam'] ?? '')),
            'away_iso'   => WorldCupDraw::isoFor((string)($e['strAwayTeam'] ?? '')),
            'home_score' => $hs,
            'away_score' => $as,
            'kickoff'    => trim(($e['dateEvent'] ?? '') . ' ' . ($e['strTime'] ?? '')),
            'venue'      => $e['strVenue'] ?? null,
            'round'      => $e['strRound'] ?? null,
            'status'     => $status,
            'raw_status' => $e['strStatus'] ?? null,
        ];
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int)$v;
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        if (function_exists('iconv')) {
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        }
        return $s;
    }

    /**
     * Bucket season events by group letter (A..L) for the Group Stage page.
     * A match is "in" a group when both teams belong to the same group in
     * the static draw. Each match is shaped like the API endpoints.
     *
     * @return array<string, array<int, array>>
     */
    private function groupMatchesByLetter(): array
    {
        $events = $this->sports->seasonEvents();
        $draw   = WorldCupDraw::groups();

        $teamGroup = [];
        foreach ($draw as $letter => $teams) {
            foreach ($teams as $t) {
                $teamGroup[$this->normalize($t['team'])] = $letter;
            }
        }
        $aliases = [
            'south korea' => 'Korea Republic',
            'czech republic' => 'Czechia',
            'bosnia-herzegovina' => 'Bosnia and Herzegovina',
            'ivory coast' => "Côte d'Ivoire",
            'iran' => 'IR Iran',
            'cape verde' => 'Cabo Verde',
            'turkey' => 'Türkiye',
            'dr congo' => 'Congo DR',
            'curacao' => 'Curaçao',
        ];
        $resolve = function (?string $name) use ($teamGroup, $aliases): ?string {
            if (!$name) return null;
            $norm = $this->normalize($name);
            $canonical = $aliases[$norm] ?? $name;
            return isset($teamGroup[$this->normalize($canonical)]) ? $canonical : null;
        };

        $byGroup = array_fill_keys(array_keys($draw), []);
        foreach ($events as $e) {
            $h = $resolve($e['strHomeTeam'] ?? null);
            $a = $resolve($e['strAwayTeam'] ?? null);
            if (!$h || !$a) continue;
            $gh = $teamGroup[$this->normalize($h)] ?? null;
            $ga = $teamGroup[$this->normalize($a)] ?? null;
            if (!$gh || $gh !== $ga) continue;
            $byGroup[$gh][] = $this->shapeMatch($e);
        }
        // Sort each group's matches chronologically.
        foreach ($byGroup as &$list) {
            usort($list, fn($x, $y) => strcmp($x['kickoff'] ?? '', $y['kickoff'] ?? ''));
        }
        return $byGroup;
    }
}
