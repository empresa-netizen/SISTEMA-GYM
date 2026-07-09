#!/usr/bin/env bash
# =============================================================================
# Production post-deploy bootstrap for SISTEMA-GYM / MGTEAM
# Run on the application server after code sync (rsync/SSH/Envoyer).
# Usage: ./deploy.sh [--skip-migrate] [--skip-assets]
# =============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

SKIP_MIGRATE=0
SKIP_ASSETS=0
for arg in "$@"; do
  case "$arg" in
    --skip-migrate) SKIP_MIGRATE=1 ;;
    --skip-assets) SKIP_ASSETS=1 ;;
    -h|--help)
      echo "Usage: $0 [--skip-migrate] [--skip-assets]"
      exit 0
      ;;
  esac
done

echo "==> Maintenance mode"
php artisan down --retry=60 || true

echo "==> Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

if [[ "$SKIP_ASSETS" -eq 0 ]]; then
  if [[ -f package-lock.json ]]; then
    echo "==> Frontend production build"
    npm ci
    NODE_ENV=production npm run build
  else
    echo "==> Skipping assets (no package-lock.json)"
  fi
fi

if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
  echo "==> Migrations"
  php artisan migrate --force
fi

echo "==> Storage link"
php artisan storage:link 2>/dev/null || true

echo "==> Clear stale caches"
php artisan optimize:clear

echo "==> Warm production caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

echo "==> Restart queue workers (Supervisor will respawn)"
php artisan queue:restart

echo "==> Bring application up"
php artisan up

echo "==> Deploy complete."
echo "    Ensure Supervisor is running: php artisan queue:work --sleep=3 --tries=3 --max-time=3600"
