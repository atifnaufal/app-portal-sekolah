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
  if ! grep -q "^APP_KEY=" .env; then
    printf 'APP_KEY=%s\n' "$APP_KEY" >> .env
    echo "[ensure-env] APP_KEY dari env Railway ditulis ke .env"
  fi
else
  # Tidak ada APP_KEY dari platform -> generate yang stabil (sekali saja kalau .env sudah ada).
  CURRENT_KEY=$(grep -E '^APP_KEY=' .env | tail -n1 | cut -d= -f2-)
  if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = '""' ] || [ "$CURRENT_KEY" = "''" ]; then
    GEN_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    # Hapus baris APP_KEY lama (kalau ada) lalu tulis yang baru.
    sed -i '/^APP_KEY=/d' .env
    printf 'APP_KEY=%s\n' "$GEN_KEY" >> .env
    echo "[ensure-env] APP_KEY di-generate dan ditulis ke .env"
  else
    echo "[ensure-env] APP_KEY sudah ada di .env — tidak diubah"
  fi
fi

echo "[ensure-env] Selesai: .env & APP_KEY siap."
