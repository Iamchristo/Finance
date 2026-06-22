# Deployment Runbook

This is a simulated/demo platform: no real money movement, no real
brokerage or custody integration. Treat every step below with that in
mind — the goal is a correctly running demo, not a regulated financial
system.

## 1. Requirements

- PHP 8.4 with extensions: `pdo_mysql`, `bcmath`, `mbstring`, `json`,
  `ctype`, `openssl`. `bcmath` is required in every real environment —
  the `brick/math`-backed shim in `tests/bootstrap.php` is test-only and
  must never be relied on outside the test suite.
- MySQL 8.0+ (or MariaDB 10.6+) with InnoDB.
- Composer 2.x, Node 18+/npm for the asset build.
- A process manager (systemd timers, cron, or supervisor) capable of
  running scheduled CLI commands — see §5.

## 2. First-time setup

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build                     # builds resources/* into public/build/
cp .env.example .env               # then edit values below
php bin/migrate                    # applies database/migrations/*.sql in order
```

Required `.env` values (see `.env.example` for the full list):

- `APP_ENV=production`, `APP_DEBUG=false` — never run with debug on in
  production; `Application::run()` only renders the safe `errors/500`
  view either way, but debug mode is reserved for local work.
- `APP_KEY` — generate a fresh value per environment
  (`php -r "echo 'base64:'.base64_encode(random_bytes(32));"`); never
  reuse the value committed in `.env.example`.
- `DB_*` — point at the production MySQL instance/credentials. The
  configured user only needs DML + the privileges to run the migration
  runner's DDL once at deploy time; it does not need superuser grants.
- `SESSION_SECURE_COOKIE=true` once served over HTTPS.
- `SIMULATED_PLATFORM_BANNER` — keep this set. It drives the
  `.sim-banner` disclosure that must appear on every page; this is a
  product requirement, not cosmetic copy.

## 3. Web server

The document root is `public/`; everything else (`app/`, `routes/`,
`database/`, `.env`) must sit outside the web root or be denied by the
server config. There is no front-controller rewrite file checked in —
add one for your server:

**nginx**

```nginx
root /path/to/Finance/public;
index index.php;

location / {
    try_files $uri /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location ~ /\.(?!well-known) {
    deny all;
}
```

**Apache** — add `public/.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

## 4. Storage permissions

The web server user needs write access to:

- `storage/logs/` — Monolog writes `app.log` here (`App\Core\Logger`).
- `storage/cache/`
- `storage/sessions/` (if file-based sessions are configured)
- `public/uploads/` — KYC documents and property photos are served
  through an ownership-checked controller, never directly by the web
  server; confirm the directory itself still isn't web-readable
  (no `Options +Indexes`, no direct route to it).

## 5. Scheduled jobs

Four console commands (`bin/console <name>`) drive every time-based
simulation. None of them are daemons — they're meant to be invoked on a
schedule and exit:

| Command                        | Suggested interval | Purpose                                                |
|---------------------------------|--------------------|---------------------------------------------------------|
| `forex:tick-prices`             | every 3–5 seconds  | Advances the simulated price feed one tick per instrument. |
| `forex:evaluate-positions`      | every 3–5 seconds, right after the tick | SL/TP/margin-call checks against the latest prices. |
| `investment:accrue`             | once daily          | Applies that day's ROI accrual to active subscriptions. |
| `realestate:accrue`             | once daily          | Applies that day's rental/ROI accrual to active investments. |

Idempotency note: both accrual commands and position-close are safe to
re-run or to overlap — re-running `investment:accrue`/`realestate:accrue`
for a day that's already been accrued is a no-op (gated by a
`SELECT ... FOR UPDATE` existence check against the
`UNIQUE(subscription_id/property_investment_id, accrual_date)`
constraint before any wallet credit happens), and overlapping
`forex:evaluate-positions` runs cannot double-pay the same position
close (gated by `UPDATE ... WHERE status = 'open'` on the position row).
Both guarantees are covered by `tests/Unit/AccrualIdempotencyTest.php`
and `tests/Unit/PositionCloseIdempotencyTest.php` — see §7.

Because the 3–5 second cadence is tighter than cron's one-minute
granularity, run the tick/evaluate pair from a small always-on loop
under a process supervisor (systemd service, supervisord) rather than
cron, e.g.:

```ini
# /etc/systemd/system/finance-forex-tick.service
[Unit]
Description=Finance platform forex price tick + position evaluation loop

[Service]
WorkingDirectory=/path/to/Finance
ExecStart=/bin/bash -c 'while true; do php bin/console forex:tick-prices; php bin/console forex:evaluate-positions; sleep 4; done'
Restart=always
User=www-data
```

The daily accrual commands are plain cron entries:

```cron
0 0 * * * cd /path/to/Finance && php bin/console investment:accrue >> storage/logs/cron.log 2>&1
5 0 * * * cd /path/to/Finance && php bin/console realestate:accrue >> storage/logs/cron.log 2>&1
```

## 6. Deploy steps (each release)

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php bin/migrate
sudo systemctl restart php8.4-fpm finance-forex-tick   # or your equivalent
```

`bin/migrate` only applies files not already recorded in the
`migrations` table, so it's safe to run on every deploy even when there
are no new migrations.

## 7. Pre-launch verification checklist

- `vendor/bin/phpunit` passes — covers `WalletService` credit/debit/
  transfer/insufficient-balance/invalid-amount paths, accrual cron
  idempotency, and concurrent position-close idempotency.
- `vendor/bin/phpstan analyse app --level 6` is clean (a handful of
  pre-existing "no value type specified in iterable type" notices on
  `find*By*`/`lock*` repository methods are known, tracked debt — not
  blocking).
- Manual walkthrough: register → log in → fund wallet via an admin
  ledger adjustment → transfer between sub-balances → subscribe to a
  plan / place a forex order / invest in a property → confirm the
  ledger entries and balance deltas reconcile → confirm the same data
  is visible and moderate-able from `/admin`.
- Confirm `.sim-banner` renders on every page template (marketing,
  all three dashboards, admin).
- Confirm `storage/logs/app.log` is being written and is *not*
  web-accessible.

## 8. Rollback

Application code: redeploy the previous git ref and re-run
`composer install`/`npm run build` for that ref; no destructive step is
needed since deploys don't run irreversible migrations in the common
case.

Database: migrations in this project are additive (new tables/columns),
not destructive, so the safe rollback path is redeploying the previous
application code against the same schema rather than reverse-migrating.
If a migration genuinely needs to be undone, write and apply a new
forward migration that reverses it — do not hand-edit the `migrations`
table or run raw `DROP`/`ALTER` statements against production outside
of a reviewed migration file.
