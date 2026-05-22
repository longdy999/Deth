<?php

return [
    // TheSportsDB API key. The literal "3" / "123" are public free-tier keys.
    'key' => env('SPORTSDB_KEY', '3'),

    // FIFA World Cup league id on TheSportsDB.
    'league_id' => env('SPORTSDB_LEAGUE_ID', '4429'),

    // World Cup 2026 season identifier accepted by /eventsseason.php.
    'season' => env('SPORTSDB_SEASON', '2026'),

    'base_url' => env('SPORTSDB_BASE_URL', 'https://www.thesportsdb.com/api/v1/json'),
];
