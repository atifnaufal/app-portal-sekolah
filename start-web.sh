#!/usr/bin/env bash
set -e

# Pastikan direktori penyimpanan runtime tersedia (fresh clone di Railway).
mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs bootstrap/cache

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
