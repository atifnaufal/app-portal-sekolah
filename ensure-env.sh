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
# Railway menyediakan DATABASE_URL saat MySQL service ditambah (format: mysql://user:pass@host:port/db).
# Parse & injeksi ke var individual agar Laravel terbaca.
# Kubernetes/URL-parse dipakai di sini (bukan regex bash) karena password boleh
# mengandung karakter khusus seperti '@', ':', '/' yang membuat regex gagal.
if [ -n "${DATABASE_URL:-}" ]; then
  DB_PARSE=$(php -r '
    $u = getenv("DATABASE_URL");
    $p = parse_url($u);
    if (!$p || empty($p["host"])) { exit(1); }
    $host = $p["host"];
    $port = $p["port"] ?? "3306";
    $user = $p["user"] ?? "";
    $pass = $p["pass"] ?? "";
    $db   = ltrim($p["path"] ?? "", "/");
    echo implode("\n", [$user, $pass, $host, $port, $db]);
  ' 2>/dev/null) || DB_PARSE=""

  if [ -n "$DB_PARSE" ]; then
    _DB_USER=$(printf '%s\n' "$DB_PARSE" | sed -n '1p')
    _DB_PASS=$(printf '%s\n' "$DB_PARSE" | sed -n '2p')
    _DB_HOST=$(printf '%s\n' "$DB_PARSE" | sed -n '3p')
    _DB_PORT=$(printf '%s\n' "$DB_PARSE" | sed -n '4p')
    _DB_NAME=$(printf '%s\n' "$DB_PARSE" | sed -n '5p')
    [ -n "$_DB_USER" ]     && inject_env "DB_USERNAME" "$_DB_USER"
    [ -n "$_DB_PASS" ]     && inject_env "DB_PASSWORD" "$_DB_PASS"
    [ -n "$_DB_HOST" ]     && inject_env "DB_HOST" "$_DB_HOST"
    [ -n "$_DB_PORT" ]     && inject_env "DB_PORT" "$_DB_PORT"
    [ -n "$_DB_NAME" ]     && inject_env "DB_DATABASE" "$_DB_NAME"
    echo "[ensure-env] DATABASE_URL di-parse & di-inject"
  else
    echo "[ensure-env] WARNING: Tidak bisa parse DATABASE_URL: ${DATABASE_URL}"
  fi
fi

# ===== Auto-map variabel MySQL Railway (template add-on) =====
# Saat service App ter-link ke service MySQL, Railway menyuntikkan variabel
# template seperti MYSQLHOST / MYSQLPORT / MYSQLDATABASE / MYSQLUSER /
# MYSQLPASSWORD (dan MYSQL_URL / MYSQL_HOST / MYSQL_DATABASE dsb.).
# Laravel hanya membaca DB_*, jadi kita petakan otomatis di sini agar app
# tersambung TANPA perlu user mengisi DB_* manual.
# Prioritas: nilai yang eksplisit di env (DB_HOST dsb.) tetap menang.
# Simpan nilai yang eksplisit di env (DB_* asli) agar bisa dibedakan dari autodeteksi.
_EXPLICIT_DB_HOST="${DB_HOST:-}"
_EXPLICIT_DB_PORT="${DB_PORT:-}"
_EXPLICIT_DB_DATABASE="${DB_DATABASE:-}"
_EXPLICIT_DB_USERNAME="${DB_USERNAME:-}"
_EXPLICIT_DB_PASSWORD="${DB_PASSWORD:-}"

# set_db KEY VALUE — isi DB_* hanya jika belum diisi eksplisit oleh user.
set_db() {
  local key="$1"; local val="$2"
  if [ -n "$val" ] && [ -z "${!key:-}" ]; then
    inject_env "$key" "$val"
    export "$key"="$val"
  fi
}

# 1) Ambil user/pass/host/port/db dari DATABASE_URL atau MYSQL_URL (url lengkap).
_DB_URL="${DATABASE_URL:-${MYSQL_PRIVATE_URL:-${MYSQL_URL:-${MYSQL_URL_OLD:-}}}}"
if [ -n "$_DB_URL" ]; then
  DB_PARSE=$(DATABASE_URL="$_DB_URL" php -r '
    $u = getenv("DATABASE_URL");
    $p = parse_url($u);
    if (!$p || empty($p["host"])) { exit(1); }
    echo implode("\n", [($p["user"] ?? ""), ($p["pass"] ?? ""), $p["host"], ($p["port"] ?? "3306"), ltrim($p["path"] ?? "", "/")]);
  ' 2>/dev/null) || DB_PARSE=""
  if [ -n "$DB_PARSE" ]; then
    set_db DB_USERNAME "$(printf '%s\n' "$DB_PARSE" | sed -n '1p')"
    set_db DB_PASSWORD "$(printf '%s\n' "$DB_PARSE" | sed -n '2p')"
    set_db DB_HOST     "$(printf '%s\n' "$DB_PARSE" | sed -n '3p')"
    set_db DB_PORT     "$(printf '%s\n' "$DB_PARSE" | sed -n '4p')"
    set_db DB_DATABASE "$(printf '%s\n' "$DB_PARSE" | sed -n '5p')"
  fi
fi

# 2) Fallback literal dari variabel template Railway (MYSQLHOST dsb.)
#    hanya untuk bagian yang belum terisi.
# JANGAN pakai RAILWAY_PRIVATE_DOMAIN sebagai host DB: pada service App nilainya = domain app
# sendiri sehingga mengarah ke service yang salah. Host DB hanya dari MYSQL* / URL yang benar.
set_db DB_HOST      "${MYSQLHOST:-${MYSQL_HOST:-}}"
set_db DB_PORT      "${MYSQLPORT:-${MYSQL_PORT:-3306}}"
set_db DB_DATABASE  "${MYSQLDATABASE:-${MYSQL_DATABASE:-}}"
set_db DB_USERNAME  "${MYSQLUSER:-${MYSQL_USER:-root}}"
set_db DB_PASSWORD  "${MYSQLPASSWORD:-${MYSQL_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}}"

echo "[ensure-env] host MySQL = ${DB_HOST:-<kosong>}"

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
