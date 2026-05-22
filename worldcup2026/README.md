# Live World Cup 2026 — Laravel Dashboard

A single-page dashboard that renders the **FIFA World Cup 2026** as it happens:
live scoreboard, group-stage standings (or schedule by matchday until groups
are populated), and a knockout bracket tree.

Data comes from [TheSportsDB free API](https://www.thesportsdb.com/free_sports_api).

## How rate-limit-safety works

TheSportsDB's free tier allows **30 requests/min**. The browser **never** calls
the upstream API directly — it polls our Laravel backend at `/api/snapshot`,
which in turn caches every TheSportsDB endpoint:

| Endpoint                          | Cache TTL | Effective rate |
|-----------------------------------|-----------|----------------|
| `eventsnextleague.php`            | 30s       | 2 req/min      |
| `eventspastleague.php`            | 60s       | 1 req/min      |
| `eventsseason.php?s=2026`         | 300s      | 0.2 req/min    |
| `lookupleague.php`                | 1h        | negligible     |

Even with thousands of concurrent visitors we issue **~5 requests/min** total —
well under the 30/min ceiling.

## Run locally

```bash
cd worldcup2026
composer install
cp .env.example .env       # already provided
php artisan key:generate
php artisan migrate         # creates the cache + sessions tables
php artisan serve
# open http://127.0.0.1:8000
```

## Configuration

`.env` keys consumed by `config/sportsdb.php`:

```
SPORTSDB_KEY=3                # free tier shared key
SPORTSDB_LEAGUE_ID=4429        # FIFA World Cup
SPORTSDB_SEASON=2026
SPORTSDB_BASE_URL=https://www.thesportsdb.com/api/v1/json
```

## Endpoints

* `GET /` — dashboard HTML shell
* `GET /api/snapshot` — JSON payload consumed by the front-end every 10 s

## Notes

* The free tier does not expose true live-scores; results refresh once the
  match is over. Upgrading the API key (Premium = 2-min livescore) will start
  populating the **Live** section automatically — no code change required.
* When TheSportsDB labels matches with `strRound = "Group A"` the dashboard
  switches from the *Schedule by Matchday* fallback to a real points table.
