<?php

use Illuminate\Support\Facades\Schedule;

/*
 * Production needs one cron entry: * * * * * php artisan schedule:run
 * Times are the app timezone (UTC, which is Ghana time).
 */

// Nightly database and photo backups, keeping a rolling history.
Schedule::command('backup:clean')->dailyAt('01:00')->onOneServer();
Schedule::command('backup:run')->dailyAt('01:30')->onOneServer();

// Emails the owner if the latest backup is missing, too old or too big.
Schedule::command('backup:monitor')->dailyAt('07:00')->onOneServer();

// Reserved items not collected in time go back on sale (see RefundPolicy).
Schedule::command('requests:release-uncollected')->dailyAt('06:00')->onOneServer();

// Online reservations: unpaid holds after 30 minutes, uncollected ones (full refund) after the collection window.
Schedule::command('reservations:release')->everyFiveMinutes()->withoutOverlapping()->onOneServer();

// Housekeeping.
Schedule::command('queue:prune-failed --hours=720')->weekly();
Schedule::command('auth:clear-resets')->daily();
