#!/usr/bin/env bash
set -e

# Realtime WebSocket Reverb untuk Railway (service terpisah).
#
# Pastikan .env & APP_KEY & semua REVERB_* terisi SEBELUM config:cache, agar
# `php artisan reverb:start` memakai kredensial yang sama dengan service web.
bash ensure-env.sh

echo "Clearing stale config cache (agar REVERB_* env terpakai)..."
php artisan config:clear --no-interaction || true

# Bind ke 0.0.0.0 dan port dari PORT (Railway service port) atau REVERB_SERVER_PORT.
# Default 8080 bila tidak diset.
REVERB_BIND_PORT="${PORT:-${REVERB_SERVER_PORT:-8080}}"

echo "Starting Reverb WebSocket server on port ${REVERB_BIND_PORT}..."
exec php artisan reverb:start --host=0.0.0.0 --port="${REVERB_BIND_PORT}"
