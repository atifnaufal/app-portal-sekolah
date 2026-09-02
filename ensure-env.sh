#!/usr/bin/env bash
# =============================================================================
# Penjaga lingkungan (env) untuk deploy Railway.
#
# Railway menyuplai variabel lingkungan lewat platform, BUKAN lewat file .env.
# Karena file .env TIDAK ikut ter-commit (.gitignore), beberapa tool menuntut
# file .env ada (mis. `php artisan key:generate`, `config:cache`) dan gagal
# dengan: file_get_contents(/app/.env): Failed to open stream.
#
# Script ini memastikan:
#   1. File .env SELALU ada (disalin dari .env.example bila belum ada).
#   2. APP_KEY terisi bila belum diset (dari env platform ATAU di-generate).
#      APP_KEY diset LEWAT env Railway lebih disarankan agar stabil lintas
#      deploy (kalau dibuat ulang tiap deploy, semua sesi/login bisa ter-reset).
# =============================================================================
set -e

cd "$(dirname "$0")"

# 1) Pastikan file .env ada.
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "[ensure-env] .env dibuat dari .env.example"
  else
    touch .env
    echo "[ensure-env] .env dibuat kosong (.env.example tidak ditemukan)"
  fi
fi

# 2) Pastikan APP_KEY terisi.
#    Prioritas: env platform (sudah ada di environment) > generate & sisipkan ke .env.
if [ -n "${APP_KEY:-}" ]; then
  # APP_KEY dari env Railway lebih unggul; pastikan tercatat di .env juga
  # (supaya tool yang membaca .env menemukannya).
  # Hapus baris APP_KEY lama dulu (termasuk yang kosong), lalu tulis yang baru.
  sed -i '/^APP_KEY=/d' .env
  printf 'APP_KEY=%s\n' "$APP_KEY" >> .env
  echo "[ensure-env] APP_KEY dari env Railway ditulis ke .env"
else
  # Tidak ada APP_KEY dari platform -> generate yang stabil (sekali saja kalau .env sudah ada).
  CURRENT_KEY=$(grep -E '^APP_KEY=' .env | tail -n1 | cut -d= -f2-)
  if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = '""' ] || [ "$CURRENT_KEY" = "''" ]; then
    GEN_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i '/^APP_KEY=/d' .env
    printf 'APP_KEY=%s\n' "$GEN_KEY" >> .env
    echo "[ensure-env] APP_KEY di-generate dan ditulis ke .env"
  else
    echo "[ensure-env] APP_KEY sudah ada di .env — tidak diubah"
  fi
fi

echo "[ensure-env] Selesai: .env & APP_KEY siap."

# 3) Inject Railway env vars penting ke .env (hanya jika kosong/tidak ada).
#    Railway menyediakan env vars lewat platform, tapi Laravel artisan/cache
#    membaca dari file .env. Script ini bridge keduanya.
inject_env() {
  local key="$1"
  local val="${2:-}"
  if [ -n "$val" ]; then
    # Hapus baris lama, tulis yang baru
    sed -i "/^${key}=/d" .env
    printf '%s="%s"\n' "$key" "$val" >> .env
  fi
}

# DB — Railway MySQL
[ -n "${DB_HOST:-}" ]     && inject_env "DB_HOST" "$DB_HOST"
[ -n "${DB_PORT:-}" ]     && inject_env "DB_PORT" "$DB_PORT"
[ -n "${DB_DATABASE:-}" ] && inject_env "DB_DATABASE" "$DB_DATABASE"
[ -n "${DB_USERNAME:-}" ] && inject_env "DB_USERNAME" "$DB_USERNAME"
[ -n "${DB_PASSWORD:-}" ] && inject_env "DB_PASSWORD" "$DB_PASSWORD"

# APP settings
[ -n "${APP_URL:-}" ]      && inject_env "APP_URL" "$APP_URL"
[ -n "${APP_ENV:-}" ]      && inject_env "APP_ENV" "$APP_ENV"
[ -n "${APP_DEBUG:-}" ]    && inject_env "APP_DEBUG" "$APP_DEBUG"

# Session & Cache
[ -n "${SESSION_DOMAIN:-}" ]          && inject_env "SESSION_DOMAIN" "$SESSION_DOMAIN"
[ -n "${SESSION_SECURE_COOKIE:-}" ]   && inject_env "SESSION_SECURE_COOKIE" "$SESSION_SECURE_COOKIE"

# Firebase
[ -n "${FIREBASE_ENABLED:-}" ]            && inject_env "FIREBASE_ENABLED" "$FIREBASE_ENABLED"
[ -n "${FIREBASE_CREDENTIALS:-}" ]        && inject_env "FIREBASE_CREDENTIALS" "$FIREBASE_CREDENTIALS"
[ -n "${FIREBASE_STORAGE_BUCKET:-}" ]     && inject_env "FIREBASE_STORAGE_BUCKET" "$FIREBASE_STORAGE_BUCKET"

# Reverb / Broadcasting
[ -n "${REVERB_APP_ID:-}" ]     && inject_env "REVERB_APP_ID" "$REVERB_APP_ID"
[ -n "${REVERB_APP_KEY:-}" ]    && inject_env "REVERB_APP_KEY" "$REVERB_APP_KEY"
[ -n "${REVERB_APP_SECRET:-}" ] && inject_env "REVERB_APP_SECRET" "$REVERB_APP_SECRET"
[ -n "${REVERB_HOST:-}" ]       && inject_env "REVERB_HOST" "$REVERB_HOST"
[ -n "${VITE_REVERB_APP_KEY:-}" ] && inject_env "VITE_REVERB_APP_KEY" "$VITE_REVERB_APP_KEY"
[ -n "${VITE_REVERB_HOST:-}" ]   && inject_env "VITE_REVERB_HOST" "$VITE_REVERB_HOST"
[ -n "${VITE_REVERB_PORT:-}" ]   && inject_env "VITE_REVERB_PORT" "$VITE_REVERB_PORT"
[ -n "${VITE_REVERB_SCHEME:-}" ] && inject_env "VITE_REVERB_SCHEME" "$VITE_REVERB_SCHEME"

# Log level — kurangi spam di production
[ -n "${LOG_LEVEL:-}" ] && inject_env "LOG_LEVEL" "$LOG_LEVEL"

echo "[ensure-env] Railway env vars di-inject ke .env"
