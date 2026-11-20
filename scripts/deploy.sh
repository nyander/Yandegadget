#!/usr/bin/env bash
# Deploys the latest code on a VPS (Forge's deploy script can simply call this).
# Run from the app directory as the web user. See docs/DEPLOYMENT.md.
set -euo pipefail

cd "$(dirname "$0")/.."

php artisan down --retry=30 --refresh=15 || true
trap 'php artisan up' EXIT

git pull --ff-only origin "${DEPLOY_BRANCH:-master}"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Node 24 is required by the frontend toolchain (.nvmrc).
npm ci
npm run build

php artisan migrate --force
# Roles, permissions, locations, categories, conditions and ledger types. Safe to re-run.
php artisan db:seed --class=ReferenceDataSeeder --force

php artisan storage:link 2>/dev/null || true
php artisan optimize
php artisan filament:optimize

# Workers pick up the new code after their current job.
php artisan queue:restart

echo "Deployed $(git rev-parse --short HEAD)"
