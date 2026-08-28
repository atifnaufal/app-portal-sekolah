#!/usr/bin/env bash
set -e

# Pastikan .env & APP_KEY ada (Railway menyuplai env lewat platform, bukan file .env).
# Mencegah error: file_get_contents(/app/.env): Failed to open stream.
bash ensure-env.sh

# Pastikan direktori penyimpanan runtime tersedia (fresh clone di Railway).
mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs storage/fonts storage/app/public bootstrap/cache

echo "Linking public storage (storage:link)..."
# Hapus dulu bila sudah ada (bisa symlink lama / file), lalu buat symlink baru.
if [ -e public/storage ] || [ -L public/storage ]; then
  rm -rf public/storage
fi
php artisan storage:link --no-interaction

echo "Clearing stale caches..."
php artisan config:clear --no-interaction || true
php artisan route:clear --no-interaction || true
php artisan view:clear --no-interaction || true

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Caching config and routes..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction

echo "Starting web server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
