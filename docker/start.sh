#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

echo "==> Migrating"
php artisan migrate --force

echo "==> Seeding"
php artisan db:seed --force

echo "==> Caching config"
php artisan config:cache
php artisan route:cache
php artisan view:cache

PORT="${PORT:-8000}"
echo "==> Starting on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
