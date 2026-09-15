# Creator Database

**A public creator database where creators can verify their real performance.**

Anyone can add a creator. Only the real creator can claim the profile — by signing in to the social
account it belongs to. Once claimed, the platform imports metrics through the platform's official API
and shows them as **verified metrics**, with the median front and centre so a single viral post doesn't
distort what a brand can expect.

Stack: Laravel 12 · PHP 8.3 · Livewire 3 · Alpine · Tailwind 4 · MySQL · queues · scheduler.

## Running locally

```bash
composer install
npm install && npm run build          # Node 20+ (Vite 7)
cp .env.example .env && php artisan key:generate
# point DB_* at a MySQL database, then:
php artisan migrate --seed
php artisan queue:work               # sync jobs run here
```

The site is served by Herd at `http://creator-database.test` (`herd link`), or use `php artisan serve`.

Seeded logins (password `password`):

| Who | Email |
| --- | --- |
| Admin | `admin@example.com` |
| Creators | `john-smith@example.com`, `lena-fischer@example.com`, `marcus-reid@example.com`, … (slug@example.com) |
| Brand user (no profile) | `brand@example.com` |

The seed creates ~30 creators across YouTube, Instagram and X: claimed and unclaimed, big-but-weak,
small-but-excellent, multi-platform, plus one "metrics outdated", one "needs reconnection" and one
"sync failed" example. Claimed creators are imported through the real job chain using the fake connector.

### No API credentials needed

`SOCIAL_CONNECTOR_DRIVER=fake` (the default) swaps in `App\Social\Fake\FakeConnector`, which implements
the exact same interface as the live connectors. Its "consent screen" (`/oauth/{platform}/authorize`)
lets you type the handle that "signs in", so you can exercise both a successful ownership check and a
mismatch. Data is generated deterministically per handle; curated demo accounts live in
`App\Social\Fake\FakeAccounts`.

To go live, set `SOCIAL_CONNECTOR_DRIVER=live` and the credentials in `.env`:

| Platform | What you need | Notes |
| --- | --- | --- |
| YouTube | Google OAuth client, YouTube Data API v3 + YouTube Analytics API enabled; optional API key | Scopes `youtube.readonly`, `yt-analytics.readonly`. The Analytics API does **not** expose impressions / thumbnail CTR, so they are never shown. Shorts are classified by duration/orientation. |
| Instagram | Meta app using *Instagram API with Instagram Login* | Scopes `instagram_business_basic`, `instagram_business_manage_insights`. Professional accounts only. Long-lived 60-day tokens, refreshed by the scheduler. `impressions`/`plays` are deprecated; `views` is used. No public username lookup exists, so unclaimed Instagram profiles show submitted data only. |
| X | Developer app, OAuth 2.0 confidential client (PKCE); optional app bearer token for public lookups | Scopes `tweet.read users.read offline.access`. Private metrics (engagements, profile/URL clicks) are only available for posts from the last 30 days. |

Redirect URI for each provider: `{APP_URL}/oauth/{youtube|instagram|x}/callback`.

**Site login** is "Continue with Google" (identity only: `openid email profile`, redirect URI
`{APP_URL}/login/google/callback`) with email + password as a fallback. Google login does not grant
YouTube access; connecting YouTube is still a separate step on the profile. One login owns one creator
profile; a profile can have one connected account per platform. Email users can reset their password
from the sign-in page (Laravel's password broker, links valid for 60 minutes).

## How it fits together

```
app/Social/
  Contracts/SocialPlatformConnector   one interface: OAuth, identity, public profile, content, metrics
  Connectors/{YouTube,Instagram,X}Connector   live implementations (HTTP only, no Eloquent)
  Fake/FakeConnector + FakeDataGenerator      local stand-in, never used when driver = live
  ConnectorManager                            resolves a connector per platform from config
  OAuth/OAuthSession                          state + PKCE + intent stored in the session
  OAuth/TokenManager                          encrypt/refresh tokens, mark "needs reconnection"
  Support/HandleNormalizer                    "@john" == "youtube.com/@john" == "https://…/@John/videos"

app/Actions/
  Creators/CreateCreator                      add a public profile (dedupe by provider id, then handle)
  Claims/StartClaim → CompleteClaim           claim flow; VerifyAccountOwnership is the trust rule
  Sync/ConnectAccount, StartAccountSync       store tokens, queue the chain
  Sync/DisconnectAccount                      deletion policy for verified data
  Admin/MergeCreators                         duplicate handling

app/Jobs/  SyncCreatorSocialProfile → SyncCreatorContent → SyncCreatorMetrics → CalculateCreatorPerformance
app/Services/Metrics/PerformanceCalculator    median/average per content type × window
```

**Trust rule** (`VerifyAccountOwnership`): the account that authenticated through the provider's OAuth
must be the account attached to the profile. Comparison is on the provider's canonical account ID
whenever we have one (stored, or resolved through a public API); only when the platform offers no
public lookup do we compare the provider-returned handle. Email is never used.

**Public vs verified data**: `creator_social_accounts` holds public fields (handle, follower count,
avatar) that anyone may have submitted. Verified data — `social_contents.metrics`,
`creator_metric_snapshots`, `creator_performance_metrics` — only ever comes from an authenticated sync
and is deleted when the creator disconnects. The UI labels the two explicitly and never implies a
platform's own verification badge.

**Audience and watch insights**: the sync also stores per-item time series (`social_contents.insights`:
a 21-point retention curve and 30 days of daily views, YouTube only) and an account-level audience
breakdown (`creator_audience_insights`: age, gender, country, city, device, follower vs non-follower
reach, 28/30-day totals). `PerformanceCalculator` averages these into a retention curve, a view-velocity
curve, first-week medians, reactions per 1,000 views and posting cadence, stored in
`creator_performance_metrics.extra`. `RankMetric` + `CreatorRankings` turn the summary columns on
`creators` into the home page ranking and the profile rank cards. X exposes no audience data, so its
profile shows per-post metrics only.

**Metric history**: every sync appends snapshots (account-level and per item) instead of overwriting.
`creator_performance_metrics` holds precomputed rows per account × content type × window
(last 10/20/30 items, last 30/90 days); a handful of columns are denormalised onto `creators` for fast
marketplace filtering and sorting.

## Scheduler

```bash
php artisan schedule:work
```

`social:sync-due` re-syncs connected accounts every `SOCIAL_SYNC_REFRESH_HOURS` (default 24) and
refreshes expiring tokens along the way. Creators can also trigger a manual sync, rate limited by
`SOCIAL_SYNC_MANUAL_COOLDOWN` minutes.

## Tests

```bash
php artisan test
```

Covers handle normalisation, duplicate prevention, the claim flow (match, mismatch, forged state,
denied consent, already claimed), the sync chain and failure states, median/average calculations,
retention/velocity/cadence maths, rankings, marketplace filters and per-platform columns, creator
editing permissions, contact requests, profile states, Google login and password reset. The live
connectors are tested against recorded response shapes with `Http::fake()`; nothing ever hits a real
API.
# trustedinfluencers
