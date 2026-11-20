# Deploying Yande Gadgets

The app is a standard Laravel 13 app. It needs:

- PHP 8.4+ with `intl`, `gd` or `imagick` (photo conversions), `bcmath`, `pdo_mysql` (or `pdo_pgsql`)
- MySQL 8 (or PostgreSQL 15+) — SQLite is for local development only
- Node 24 to build the frontend (`.nvmrc`)
- `mysqldump` (or `pg_dump`) on the server, for backups
- A queue worker, the scheduler (one cron entry), and HTTPS

Two ways to host it. Both work with this repo as it is; the choice is budget versus how much server work you want.

|                     | Laravel Forge + a VPS                                              | Laravel Cloud                                                          |
| ------------------- | ------------------------------------------------------------------ | ---------------------------------------------------------------------- |
| Cost                | VPS (e.g. a 2 GB DigitalOcean or Hetzner box) + Forge subscription | Pay for usage; more per month at this size                             |
| You look after      | OS updates (Forge automates most), PHP version, disk space         | Very little                                                            |
| Queue and scheduler | Forge daemon + scheduler tick box                                  | Built in                                                               |
| Photos              | Local disk is fine at first; move to R2 later                      | Use an object storage bucket from the start (no persistent local disk) |

## Environment

Start from `.env.example`. In production set at least:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your domain>
APP_KEY=                      # php artisan key:generate --show

DB_CONNECTION=mysql
DB_HOST=… DB_DATABASE=… DB_USERNAME=… DB_PASSWORD=…

SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database          # or redis

MAIL_MAILER=resend           # the app emails invitations, password resets and request updates
RESEND_API_KEY=re_…
MAIL_FROM_ADDRESS=…           # an address on the domain verified in Resend

PAYMENTS_DRIVER=paystack
PAYSTACK_PUBLIC_KEY=pk_live_…
PAYSTACK_SECRET_KEY=sk_live_…

SHOP_CONTACT_EMAIL=…
SHOP_CONTACT_PHONE=…

SENTRY_LARAVEL_DSN=…          # optional but recommended
BACKUP_DISKS=local,backups
BACKUP_NOTIFY_EMAIL=…
BACKUP_ARCHIVE_PASSWORD=…     # keep a copy somewhere safe, off the server
BACKUP_S3_…=…                 # a private bucket, separate from photos
```

The fake payment gateway refuses to run in production, so `PAYMENTS_DRIVER=paystack` is required there.

## First deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate          # only if APP_KEY is empty
php artisan migrate --force
php artisan db:seed --force       # reference data and carriers only; demo data never runs in production
php artisan storage:link
php artisan shop:create-admin owner@example.com "Owner's name"
php artisan optimize
php artisan filament:optimize
```

`shop:create-admin` emails a link to set the password, so mail must be working. Then sign in, and in the admin panel:

1. **Settings → Exchange rates:** enter today's GBP → GHS rate. Without one, the shop shows prices in pounds.
2. **Catalogue → Conditions:** check the deposit for each grade. They are still the 2020 amounts.
3. **Shop settings:** confirm the negotiation floor (70% by default).
4. **Settings → Users → Add a person:** for each member of staff, with their store.

## Every deploy after that

`scripts/deploy.sh` does the steps above in order (maintenance mode, pull, install, build, migrate, reference data, caches, restart workers). On Forge, set the deploy script to:

```bash
cd /home/forge/<site>
DEPLOY_BRANCH=master ./scripts/deploy.sh
```

On Laravel Cloud, use these as the build commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

and these as the deploy commands:

```bash
php artisan migrate --force
php artisan db:seed --class=ReferenceDataSeeder --force
php artisan filament:optimize
```

## Queue worker and scheduler

- **Worker:** `php artisan queue:work --tries=3 --max-time=3600`. Notifications and emails are queued; without a worker nobody gets them. On Forge add it as a daemon; on Cloud enable a worker.
- **Scheduler:** one cron entry, `* * * * * cd /path/to/app && php artisan schedule:run`. It runs the nightly backup (01:30), backup cleanup (01:00) and a morning backup health check that emails `BACKUP_NOTIFY_EMAIL` if anything is wrong. See `routes/console.php`.

## Paystack

1. In the Paystack dashboard, switch to **Live** and copy the live keys into `.env`.
2. Settings → API Keys & Webhooks: set the **Webhook URL** to `https://<your domain>/webhooks/paystack`. The callback URL is set per payment by the app, so leave it empty.
3. Make a real deposit of the smallest amount from a customer account, then refund it from the admin panel (Customer requests → Cancel with refund). Check the payment shows as paid, then refunded, under Sales → Online payments.

## Photos

Photos go on the `public` disk (`MEDIA_DISK=public`) with WebP thumbnails made straight away. That's fine on a VPS. To move them to object storage (Cloudflare R2 is cheapest for serving images), set the `AWS_*` variables to the bucket, add `AWS_ENDPOINT` and `AWS_URL` (the bucket's public URL), and set `MEDIA_DISK=s3`. Existing photos need copying across first.

## Backups

- What: the database and `storage/app/public` (photos). Code is in git; `.env` is not backed up, so keep a copy in a password manager.
- Where: `BACKUP_DISKS`. Use `local,backups` in production so a copy leaves the server.
- How long: every backup for 7 days, then daily for 16 days, weekly for 8 weeks and monthly for 4 months.
- Check by hand: `php artisan backup:list`.
- **Test a restore before launch:** download the latest zip, unzip it with the archive password, import the SQL dump into a scratch database and open a few records.

## Errors and health

- `/up` returns 200 when the app boots. Point an uptime monitor (e.g. Better Stack, UptimeRobot, Forge's own) at it.
- With `SENTRY_LARAVEL_DSN` set, unhandled errors go to Sentry. Personal data isn't sent (`SENTRY_SEND_DEFAULT_PII` is off).
- Laravel's log is `storage/logs/laravel.log`. Use `LOG_STACK=daily` in production so it rotates.

## Behind a proxy

Set `TRUSTED_PROXIES=*` on Laravel Cloud or behind a load balancer, so the app sees HTTPS and the real visitor IP (used for login rate limiting). On a plain VPS with nginx in front, leave it empty. Behind Cloudflare's proxy, list Cloudflare's IP ranges.
