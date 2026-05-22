<?php

namespace App\Support;

/**
 * Static reference data for the FIFA World Cup 2026.
 *
 * TheSportsDB's free tier doesn't yet publish the group letters or the
 * knockout pairings for 2026, so we hard-code the official draw and the
 * 104-match bracket plumbing. The controller overlays live scores from
 * the API on top of this scaffold as fixtures are played.
 *
 * Sources:
 *  - FIFA official 2026 standings page (groups & matchday calendar)
 *  - 2026 FIFA World Cup format: 12 groups of 4, top 2 + 8 best 3rds → R32
 */
final class WorldCupDraw
{
    /**
     * 12 groups × 4 teams. ISO codes are used to load flags from
     * https://flagcdn.com  (Scotland & England use the ISO 3166-2 GB
     * sub-codes that flagcdn supports out of the box).
     *
     * @return array<string, array<int, array{team:string, iso:string}>>
     */
    public static function groups(): array
    {
        return [
            'A' => [
                ['team' => 'Mexico',                  'iso' => 'mx'],
                ['team' => 'South Africa',            'iso' => 'za'],
                ['team' => 'Korea Republic',          'iso' => 'kr'],
                ['team' => 'Czechia',                 'iso' => 'cz'],
            ],
            'B' => [
                ['team' => 'Canada',                  'iso' => 'ca'],
                ['team' => 'Bosnia and Herzegovina',  'iso' => 'ba'],
                ['team' => 'Qatar',                   'iso' => 'qa'],
                ['team' => 'Switzerland',             'iso' => 'ch'],
            ],
            'C' => [
                ['team' => 'Brazil',                  'iso' => 'br'],
                ['team' => 'Morocco',                 'iso' => 'ma'],
                ['team' => 'Haiti',                   'iso' => 'ht'],
                ['team' => 'Scotland',                'iso' => 'gb-sct'],
            ],
            'D' => [
                ['team' => 'USA',                     'iso' => 'us'],
                ['team' => 'Paraguay',                'iso' => 'py'],
                ['team' => 'Australia',               'iso' => 'au'],
                ['team' => 'Türkiye',                 'iso' => 'tr'],
            ],
            'E' => [
                ['team' => 'Germany',                 'iso' => 'de'],
                ['team' => 'Curaçao',                 'iso' => 'cw'],
                ['team' => "Côte d'Ivoire",           'iso' => 'ci'],
                ['team' => 'Ecuador',                 'iso' => 'ec'],
            ],
            'F' => [
                ['team' => 'Netherlands',             'iso' => 'nl'],
                ['team' => 'Japan',                   'iso' => 'jp'],
                ['team' => 'Sweden',                  'iso' => 'se'],
                ['team' => 'Tunisia',                 'iso' => 'tn'],
            ],
            'G' => [
                ['team' => 'Belgium',                 'iso' => 'be'],
                ['team' => 'Egypt',                   'iso' => 'eg'],
                ['team' => 'IR Iran',                 'iso' => 'ir'],
                ['team' => 'New Zealand',             'iso' => 'nz'],
            ],
            'H' => [
                ['team' => 'Spain',                   'iso' => 'es'],
                ['team' => 'Cabo Verde',              'iso' => 'cv'],
                ['team' => 'Saudi Arabia',            'iso' => 'sa'],
                ['team' => 'Uruguay',                 'iso' => 'uy'],
            ],
            'I' => [
                ['team' => 'France',                  'iso' => 'fr'],
                ['team' => 'Senegal',                 'iso' => 'sn'],
                ['team' => 'Iraq',                    'iso' => 'iq'],
                ['team' => 'Norway',                  'iso' => 'no'],
            ],
            'J' => [
                ['team' => 'Argentina',               'iso' => 'ar'],
                ['team' => 'Algeria',                 'iso' => 'dz'],
                ['team' => 'Austria',                 'iso' => 'at'],
                ['team' => 'Jordan',                  'iso' => 'jo'],
            ],
            'K' => [
                ['team' => 'Portugal',                'iso' => 'pt'],
                ['team' => 'Congo DR',                'iso' => 'cd'],
                ['team' => 'Uzbekistan',              'iso' => 'uz'],
                ['team' => 'Colombia',                'iso' => 'co'],
            ],
            'L' => [
                ['team' => 'England',                 'iso' => 'gb-eng'],
                ['team' => 'Croatia',                 'iso' => 'hr'],
                ['team' => 'Ghana',                   'iso' => 'gh'],
                ['team' => 'Panama',                  'iso' => 'pa'],
            ],
        ];
    }

    /**
     * Knockout bracket. Each match has an `id` (FIFA's M-number), the two
     * slot codes (1E = "winner of Group E", 3ABCDF = "best 3rd-place from
     * groups A/B/C/D/F", W74 = "winner of match 74", RU101 = "runner-up
     * of match 101"), and the scheduled kickoff (UTC).
     *
     * Layout matches the FIFA official site: left half (top→bottom),
     * right half (top→bottom), then convergence rounds.
     *
     * @return array<string, array<int, array{id:string, date:string, home:string, away:string}>>
     */
    public static function bracket(): array
    {
        return [
            'r32' => [
                // Left half
                ['id' => 'M74', 'date' => '2026-06-30 03:30', 'home' => '1E',  'away' => '3ABCDF'],
                ['id' => 'M77', 'date' => '2026-07-01 04:00', 'home' => '1I',  'away' => '3CDFGH'],
                ['id' => 'M73', 'date' => '2026-06-29 02:00', 'home' => '2A',  'away' => '2B'],
                ['id' => 'M75', 'date' => '2026-06-30 08:00', 'home' => '1F',  'away' => '2C'],
                ['id' => 'M83', 'date' => '2026-07-03 06:00', 'home' => '2K',  'away' => '2L'],
                ['id' => 'M84', 'date' => '2026-07-03 02:00', 'home' => '1H',  'away' => '2J'],
                ['id' => 'M81', 'date' => '2026-07-02 07:00', 'home' => '1D',  'away' => '3BEFIJ'],
                ['id' => 'M82', 'date' => '2026-07-02 03:00', 'home' => '1G',  'away' => '3AEHIJ'],
                // Right half
                ['id' => 'M76', 'date' => '2026-06-30 00:00', 'home' => '1C',  'away' => '2F'],
                ['id' => 'M78', 'date' => '2026-07-01 00:00', 'home' => '2E',  'away' => '2I'],
                ['id' => 'M79', 'date' => '2026-07-01 08:00', 'home' => '1A',  'away' => '3CEFHI'],
                ['id' => 'M80', 'date' => '2026-07-01 23:00', 'home' => '1L',  'away' => '3EHIJK'],
                ['id' => 'M86', 'date' => '2026-07-04 05:00', 'home' => '1J',  'away' => '2H'],
                ['id' => 'M88', 'date' => '2026-07-04 01:00', 'home' => '2D',  'away' => '2G'],
                ['id' => 'M85', 'date' => '2026-07-03 10:00', 'home' => '1B',  'away' => '3EFGIJ'],
                ['id' => 'M87', 'date' => '2026-07-04 08:30', 'home' => '1K',  'away' => '3DEIJL'],
            ],
            'r16' => [
                ['id' => 'M89', 'date' => '2026-07-05 04:00', 'home' => 'W74', 'away' => 'W77'],
                ['id' => 'M90', 'date' => '2026-07-05 00:00', 'home' => 'W73', 'away' => 'W75'],
                ['id' => 'M93', 'date' => '2026-07-07 02:00', 'home' => 'W83', 'away' => 'W84'],
                ['id' => 'M94', 'date' => '2026-07-07 07:00', 'home' => 'W81', 'away' => 'W82'],
                ['id' => 'M91', 'date' => '2026-07-06 03:00', 'home' => 'W76', 'away' => 'W78'],
                ['id' => 'M92', 'date' => '2026-07-06 07:00', 'home' => 'W79', 'away' => 'W80'],
                ['id' => 'M95', 'date' => '2026-07-07 23:00', 'home' => 'W86', 'away' => 'W88'],
                ['id' => 'M96', 'date' => '2026-07-08 03:00', 'home' => 'W85', 'away' => 'W87'],
            ],
            'qf' => [
                ['id' => 'M97',  'date' => '2026-07-10 03:00', 'home' => 'W89', 'away' => 'W90'],
                ['id' => 'M98',  'date' => '2026-07-11 02:00', 'home' => 'W93', 'away' => 'W94'],
                ['id' => 'M99',  'date' => '2026-07-12 04:00', 'home' => 'W91', 'away' => 'W92'],
                ['id' => 'M100', 'date' => '2026-07-12 08:00', 'home' => 'W95', 'away' => 'W96'],
            ],
            'sf' => [
                ['id' => 'M101', 'date' => '2026-07-15 02:00', 'home' => 'W97', 'away' => 'W98'],
                ['id' => 'M102', 'date' => '2026-07-16 02:00', 'home' => 'W99', 'away' => 'W100'],
            ],
            'third' => [
                ['id' => 'M103', 'date' => '2026-07-19 04:00', 'home' => 'RU101', 'away' => 'RU102'],
            ],
            'final' => [
                ['id' => 'M104', 'date' => '2026-07-20 02:00', 'home' => 'W101', 'away' => 'W102'],
            ],
        ];
    }

    /** Lookup: team name → ISO code (for live data overlay & flag rendering). */
    public static function isoFor(string $teamName): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            foreach (self::groups() as $teams) {
                foreach ($teams as $t) {
                    $map[self::normalize($t['team'])] = $t['iso'];
                }
            }
            // Aliases (TheSportsDB uses some different spellings).
            $aliases = [
                'south korea'      => 'kr',
                'czech republic'   => 'cz',
                'bosnia-herzegovina' => 'ba',
                'ivory coast'      => 'ci',
                'iran'             => 'ir',
                'cape verde'       => 'cv',
                'turkey'           => 'tr',
                'dr congo'         => 'cd',
                'congo dr'         => 'cd',
                'curacao'          => 'cw',
            ];
            $map = array_merge($map, $aliases);
        }
        return $map[self::normalize($teamName)] ?? null;
    }

    private static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        // Strip diacritics for robust matching.
        if (function_exists('iconv')) {
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        }
        return $s;
    }
}
