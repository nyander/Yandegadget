# Yande Gadgets

Second-hand electronics bought in the UK and sold from the store in Legon, Accra. Laravel 13 with a React (Inertia) shop and staff app, and a Filament back office at `/admin`.

- How the app works and its conventions: [`CLAUDE.md`](CLAUDE.md)
- Plan and decisions: [`docs/REBUILD_PLAN.md`](docs/REBUILD_PLAN.md)
- Going live: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md), [`docs/LAUNCH_CHECKLIST.md`](docs/LAUNCH_CHECKLIST.md)
- Training: [`docs/STAFF_GUIDE.md`](docs/STAFF_GUIDE.md)

Keep the project outside iCloud-synced folders (`Documents`, `Desktop`): iCloud offloads files and breaks `vendor`, `node_modules` and the database.

## Running it locally

You need PHP 8.4+ and Composer, and Node 24 (`nvm install 24`). The frontend tools don't run on Node 20.

### First time

```bash
nvm use
composer setup
```

That installs everything, creates `.env`, sets up the SQLite database with demo data, links the photo folder and builds the frontend.

### Every time

```bash
nvm use
composer dev
```

Open http://127.0.0.1:8000. `composer dev` runs these together; stop them all with Ctrl+C:

- **server:** the site
- **vite:** reloads the page when you change React or CSS files
- **queue:** sends notifications and emails
- **logs:** live log; emails appear here instead of being sent (`MAIL_MAILER=log`)
- **reverb:** live updates, so open pages redraw as soon as something changes. They're on when `.env` has `BROADCAST_CONNECTION=reverb` (run `php artisan reverb:install` once to fill in the `REVERB_*` values, then `npm run build`). With `log`, pages check every few seconds instead.

`php artisan serve` on its own also works after `npm run build`, but without the queue no emails or notifications go out, and frontend changes need another build.

### Demo accounts

Password `password` for all of them:

| Email                   | Can do                                            |
| ----------------------- | ------------------------------------------------- |
| `admin@example.test`    | Everything, including the back office at `/admin` |
| `staff@example.test`    | Check in shipments and sell at `/staff`           |
| `customer@example.test` | Browse, request items, pay deposits               |
| `supplier@example.test` | Offer stock to the shop                           |

Deposits use a pretend payment provider locally (`PAYMENTS_DRIVER=fake`): "Pay deposit" succeeds straight away, no card needed.

### Start again with fresh demo data

```bash
php artisan migrate:fresh --seed
```

### Scheduled jobs

Backups and releasing uncollected items run on a schedule in production. To try them locally:

```bash
php artisan schedule:work
```

Or run one directly, for example `php artisan requests:release-uncollected`.

### Checks

```bash
composer ci:check
```

Runs formatting, linting, type checks, static analysis and the test suite, the same as CI.
