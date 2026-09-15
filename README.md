# Trusted Influencers

**A public creator database where creators can verify their real performance.**

Anyone can add a creator. Only the real creator can claim the profile, by signing in with the social
account it belongs to. Once claimed, the platform pulls the numbers straight from YouTube, Instagram or
X and shows them as verified: median views, how much of a video gets watched, who the audience is,
engagement, posting cadence. Brands get numbers nobody typed in.

Laravel 12 · PHP 8.3 · Livewire 3 · Tailwind 4 · MySQL · queues · scheduler.

## Local setup

```bash
composer install
npm install && npm run build            # Node 20+
cp .env.example .env && php artisan key:generate
# point DB_* at a MySQL database, then:
php artisan migrate --seed
php artisan queue:work                  # imports run here
```

Serve it with Herd (`herd link`, then https://trustedinfluencers.test) or `php artisan serve`.

Out of the box `SOCIAL_CONNECTOR_DRIVER=fake`: every OAuth screen is replaced by a local
"sign in as…" page, so the whole product (claiming, mismatches, imports, Google login) works without
any API keys or app approval.

### Demo logins (password `password`)

| Who | Email |
| --- | --- |
| Admin | `admin@example.com` |
| Creators | `john-smith@example.com`, `lena-fischer@example.com`, `marcus-reid@example.com`, … (`<slug>@example.com`) |
| Brand user, no profile | `brand@example.com` |

The seed has ~30 creators across the three platforms: claimed and unclaimed, big-but-weak,
small-but-excellent, multi-platform, plus one "metrics outdated", one "needs reconnection" and one
"sync failed". Claimed ones are imported through the real job chain with the fake connector, so their
data is shaped exactly like a live import.

## How it works

1. **Anyone adds a creator**: name, platform, handle. Handles and URLs are normalised
   (`@john` = `youtube.com/@John/videos`) so the same account can't be listed twice. The profile is
   public straight away and shows public info only.
2. **The creator claims it**: they log in (Google, or email + password), open their profile, press
   *Claim*, and sign in with the social account itself.
3. **It has to be the right account**: the provider's canonical account id (YouTube channel id,
   Instagram professional account id, X user id) must match the profile. Email is never used as proof.
4. **The numbers come from the platform**: content, per-item analytics, retention curves, daily views,
   audience breakdowns. Imported by queued jobs, refreshed daily, never editable.

One login owns one creator profile. A profile can have one connected account per platform.

## Going live

Set `SOCIAL_CONNECTOR_DRIVER=live` and fill in the credentials in `.env` (`.env.example` lists every
variable with where to get it and which redirect URI to register).

| Platform | You need | Good to know |
| --- | --- | --- |
| Google login | OAuth client in Google Cloud | Identity only. Reuses the YouTube client if left empty. |
| YouTube | Same project, YouTube Data API v3 + YouTube Analytics API enabled | Scopes `youtube.readonly` + `yt-analytics.readonly`; Google requires OAuth verification before the public can use them. Impressions/thumbnail CTR are not in the API, so never shown. |
| Instagram | Meta app using *Instagram API with Instagram Login* | Professional accounts only. `instagram_business_basic` + `instagram_business_manage_insights`, App Review required. No public username lookup, so unclaimed Instagram profiles show submitted info only. |
| X | Developer app, OAuth 2.0 confidential client | Reading posts needs a paid tier. Private metrics (engagements, profile/link clicks) only exist for posts from the last 30 days. No audience demographics. |

Production checklist:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`, `SESSION_SECURE_COOKIE=true`
- A real mailer (`MAIL_MAILER=smtp` or postmark/resend): contact requests and password resets go out by email
- Cron, every minute: `cd /path/to/app && php artisan schedule:run`. That single line runs the
  hourly re-sync, the cleanup and the queue worker (a short-lived `queue:work` each minute that
  handles imports and emails). If you'd rather run a permanent worker under Supervisor, drop the
  `queue:work` line from `routes/console.php`.
- `php artisan optimize` after deploy, `npm run build` for assets

## Code map

```
app/Social/
  Contracts/SocialPlatformConnector    one interface: OAuth, identity, public profile, content, metrics
  Connectors/{YouTube,Instagram,X}     live implementations, HTTP only
  Fake/                                offline stand-in, deterministic data, curated demo accounts
  Login/                               Google sign-in (live + fake)
  OAuth/OAuthSession, TokenManager     state + PKCE in the session; encrypted tokens, refresh, reconnection
  Support/HandleNormalizer             canonical handles per platform

app/Actions/                           one class per user action (create, claim, connect, sync, merge, contact)
app/Jobs/                              SyncCreatorSocialProfile → SyncCreatorContent → SyncCreatorMetrics
                                       → CalculateCreatorPerformance (chained per account)
app/Services/Metrics/                  Statistics, PerformanceCalculator (median/average per content type
                                       and window, retention and velocity curves, cadence), CreatorRankings
app/View/                              ProfileInsights and AudienceSummary: derived numbers for the views
app/Livewire/                          marketplace, profile, add creator, account area, admin, auth
```

**Public vs verified data.** `creator_social_accounts` holds public fields anyone may have submitted.
Verified data (`social_contents.metrics` + `insights`, `creator_metric_snapshots`,
`creator_audience_insights`, `creator_performance_metrics`) only comes from an authenticated sync and is
deleted when the creator disconnects. The UI labels the two everywhere and never implies the platform's
own verification badge.

**History.** Every sync appends snapshots instead of overwriting. `creator_performance_metrics` holds
precomputed rows per account × content type × window (last 10/20/30 items, last 30/90 days). A few
columns are denormalised onto `creators` for fast sorting and the rankings.

## Tests

```bash
php artisan test
```

125 tests: handle normalisation, duplicate prevention, the claim flow (match, mismatch, forged state,
denied consent, already claimed), the sync chain and failure states, the maths (median, curves,
cadence, rankings), marketplace filters and per-platform columns, editing permissions, contact
requests, profile states, Google login, password reset. The live connectors run against recorded
response shapes with `Http::fake()`. Nothing ever calls a real API.
