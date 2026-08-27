#!/usr/bin/env bash
set -e

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Caching config and routes..."
php artisan config:cache --no-interaction || true
php artisan route:cache --no-interaction || true

echo "Starting web server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
