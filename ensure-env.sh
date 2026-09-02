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
#   2. APP_KEY terisi & format valid (base64: prefix).
#   3. DB terkoneksi (parse DATABASE_URL atau auto-map MYSQL* vars).
# =============================================================================
set -e

cd "$(dirname "$0")"

echo "=========================================="
echo "[ensure-env] Mulai — $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# Helper: is_valid_value — return 0 jika value valid (bukan template string).
is_valid_val() {
  local v="${1:-}"
  [ -n "$v" ] && [[ ! "$v" =~ [\$\{] ]] && [[ ! "$v" =~ ^\{\{ ]] && [[ ! "$v" =~ \}\}$ ]]
}

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

# 2) Pastikan APP_KEY terisi & format valid (base64: prefix wajib).
if [ -n "${APP_KEY:-}" ]; then
  # Auto-tambah base64: prefix jika missing
  if [[ "$APP_KEY" != base64:* ]]; then
    APP_KEY="base64:${APP_KEY}"
    echo "[ensure-env] APP_KEY auto-tambah prefix base64:"
  fi
  sed -i '/^APP_KEY=/d' .env
  printf 'APP_KEY=%s\n' "$APP_KEY" >> .env
  echo "[ensure-env] APP_KEY dari env Railway ditulis ke .env"
else
  CURRENT_KEY=$(grep -E '^APP_KEY=' .env | tail -n1 | cut -d= -f2-)
  if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = '""' ] || [ "$CURRENT_KEY" = "''" ]; then
    GEN_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i '/^APP_KEY=/d' .env
    printf 'APP_KEY=%s\n' "$GEN_KEY" >> .env
    echo "[ensure-env] APP_KEY di-generate dan ditulis ke .env"
  else
    # Validate existing key has base64: prefix
    if [[ "$CURRENT_KEY" != base64:* ]]; then
      sed -i '/^APP_KEY=/d' .env
      printf 'APP_KEY=base64:%s\n' "$CURRENT_KEY" >> .env
      echo "[ensure-env] APP_KEY existing ditambah prefix base64:"
    else
      echo "[ensure-env] APP_KEY sudah ada & valid — tidak diubah"
    fi
  fi
fi

echo "[ensure-env] APP_KEY siap."

# 3) Inject env vars ke .env
inject_env() {
  local key="$1"
  local val="${2:-}"
  if [ -n "$val" ]; then
    sed -i "/^${key}=/d" .env
    printf '%s="%s"\n' "$key" "$val" >> .env
  fi
}

# Helper: safe_inject — hanya inject jika value valid (bukan template string).
safe_inject() {
  local key="$1"
  local val="${2:-}"
  if is_valid_val "$val"; then
    inject_env "$key" "$val"
    export "$key"="$val"
  else
    echo "[ensure-env] SKIP $key = \"$val\" (template string, belum di-resolve)"
  fi
}

# 4) DB — parse DATABASE_URL (prioritas utama).
_DB_URL="${DATABASE_URL:-${MYSQL_PRIVATE_URL:-${MYSQL_URL:-${MYSQL_URL_OLD:-}}}}"
if [ -n "$_DB_URL" ]; then
  DB_PARSE=$(DATABASE_URL="$_DB_URL" php -r '
    $u = getenv("DATABASE_URL");
    $p = parse_url($u);
    if (!$p || empty($p["host"])) { exit(1); }
    echo implode("\n", [($p["user"] ?? ""), ($p["pass"] ?? ""), $p["host"], ($p["port"] ?? "3306"), ltrim($p["path"] ?? "", "/")]);
  ' 2>/dev/null) || DB_PARSE=""

  if [ -n "$DB_PARSE" ]; then
    _DB_USER=$(printf '%s\n' "$DB_PARSE" | sed -n '1p')
    _DB_PASS=$(printf '%s\n' "$DB_PARSE" | sed -n '2p')
    _DB_HOST=$(printf '%s\n' "$DB_PARSE" | sed -n '3p')
    _DB_PORT=$(printf '%s\n' "$DB_PARSE" | sed -n '4p')
    _DB_NAME=$(printf '%s\n' "$DB_PARSE" | sed -n '5p')
    [ -n "$_DB_USER" ]     && inject_env "DB_USERNAME" "$_DB_USER" && export DB_USERNAME="$_DB_USER"
    [ -n "$_DB_PASS" ]     && inject_env "DB_PASSWORD" "$_DB_PASS" && export DB_PASSWORD="$_DB_PASS"
    [ -n "$_DB_HOST" ]     && inject_env "DB_HOST" "$_DB_HOST" && export DB_HOST="$_DB_HOST"
    [ -n "$_DB_PORT" ]     && inject_env "DB_PORT" "$_DB_PORT" && export DB_PORT="$_DB_PORT"
    [ -n "$_DB_NAME" ]     && inject_env "DB_DATABASE" "$_DB_NAME" && export DB_DATABASE="$_DB_NAME"
    echo "[ensure-env] DATABASE_URL di-parse: host=$_DB_HOST port=$_DB_PORT db=$_DB_NAME user=$_DB_USER"
  else
    echo "[ensure-env] WARNING: Tidak bisa parse DATABASE_URL"
  fi
else
  echo "[ensure-env] INFO: DATABASE_URL tidak diset"
fi

# 5) Fallback: auto-map MYSQL* vars → DB_* (jika DB_* belum terisi dari DATABASE_URL).
if [ -z "${DB_HOST:-}" ]; then
  [ -n "${MYSQLHOST:-}" ]        && safe_inject DB_HOST "$MYSQLHOST"
  [ -z "${DB_HOST:-}" ] && [ -n "${MYSQL_HOST:-}" ] && safe_inject DB_HOST "$MYSQL_HOST"
fi
if [ -z "${DB_PORT:-}" ]; then
  [ -n "${MYSQLPORT:-}" ]        && safe_inject DB_PORT "$MYSQLPORT"
  [ -z "${DB_PORT:-}" ] && [ -n "${MYSQL_PORT:-}" ] && safe_inject DB_PORT "$MYSQL_PORT"
fi
if [ -z "${DB_DATABASE:-}" ]; then
  [ -n "${MYSQLDATABASE:-}" ]    && safe_inject DB_DATABASE "$MYSQLDATABASE"
  [ -z "${DB_DATABASE:-}" ] && [ -n "${MYSQL_DATABASE:-}" ] && safe_inject DB_DATABASE "$MYSQL_DATABASE"
fi
if [ -z "${DB_USERNAME:-}" ]; then
  [ -n "${MYSQLUSER:-}" ]        && safe_inject DB_USERNAME "$MYSQLUSER"
  [ -z "${DB_USERNAME:-}" ] && [ -n "${MYSQL_USER:-}" ] && safe_inject DB_USERNAME "$MYSQL_USER"
fi
if [ -z "${DB_PASSWORD:-}" ]; then
  [ -n "${MYSQLPASSWORD:-}" ]    && safe_inject DB_PASSWORD "$MYSQLPASSWORD"
  [ -z "${DB_PASSWORD:-}" ] && [ -n "${MYSQL_PASSWORD:-}" ] && safe_inject DB_PASSWORD "$MYSQL_PASSWORD"
fi

echo "[ensure-env] DB_HOST=${DB_HOST:-<kosong>} DB_DATABASE=${DB_DATABASE:-<kosong>}"

# 6) APP settings — skip template strings
safe_inject APP_URL       "${APP_URL:-}"
safe_inject APP_ENV       "${APP_ENV:-}"
safe_inject APP_DEBUG     "${APP_DEBUG:-}"
safe_inject APP_NAME      "${APP_NAME:-}"

# 7) Session & Cache
safe_inject SESSION_DOMAIN          "${SESSION_DOMAIN:-}"
safe_inject SESSION_SECURE_COOKIE   "${SESSION_SECURE_COOKIE:-}"
safe_inject SESSION_DRIVER          "${SESSION_DRIVER:-}"
safe_inject SESSION_LIFETIME        "${SESSION_LIFETIME:-}"
safe_inject CACHE_STORE             "${CACHE_STORE:-}"
safe_inject QUEUE_CONNECTION        "${QUEUE_CONNECTION:-}"

# 8) Firebase
safe_inject FIREBASE_ENABLED            "${FIREBASE_ENABLED:-}"
safe_inject FIREBASE_CREDENTIALS        "${FIREBASE_CREDENTIALS:-}"
safe_inject FIREBASE_STORAGE_BUCKET     "${FIREBASE_STORAGE_BUCKET:-}"
safe_inject FIREBASE_PROJECT_ID         "${FIREBASE_PROJECT_ID:-}"
safe_inject FIREBASE_DATABASE_URL       "${FIREBASE_DATABASE_URL:-}"

# 9) Reverb / Broadcasting
safe_inject REVERB_APP_ID       "${REVERB_APP_ID:-}"
safe_inject REVERB_APP_KEY      "${REVERB_APP_KEY:-}"
safe_inject REVERB_APP_SECRET   "${REVERB_APP_SECRET:-}"
safe_inject REVERB_HOST         "${REVERB_HOST:-}"
safe_inject VITE_REVERB_APP_KEY "${VITE_REVERB_APP_KEY:-}"
safe_inject VITE_REVERB_HOST    "${VITE_REVERB_HOST:-}"
safe_inject VITE_REVERB_PORT    "${VITE_REVERB_PORT:-}"
safe_inject VITE_REVERB_SCHEME  "${VITE_REVERB_SCHEME:-}"

# 10) Log & proxy
safe_inject LOG_LEVEL     "${LOG_LEVEL:-}"
safe_inject TRUSTED_PROXIES "${TRUSTED_PROXIES:-}"
safe_inject PHP_CLI_SERVER_WORKERS "${PHP_CLI_SERVER_WORKERS:-}"

# 11) Mail
safe_inject MAIL_MAILER        "${MAIL_MAILER:-}"
safe_inject MAIL_HOST          "${MAIL_HOST:-}"
safe_inject MAIL_PORT          "${MAIL_PORT:-}"
safe_inject MAIL_SCHEME        "${MAIL_SCHEME:-}"
safe_inject MAIL_USERNAME      "${MAIL_USERNAME:-}"
safe_inject MAIL_PASSWORD      "${MAIL_PASSWORD:-}"
safe_inject MAIL_FROM_ADDRESS  "${MAIL_FROM_ADDRESS:-}"
safe_inject MAIL_FROM_NAME     "${MAIL_FROM_NAME:-}"

echo "=========================================="
echo "[ensure-env] Selesai — .env siap untuk Laravel"
echo "  DB: ${DB_HOST:-<kosong>}:${DB_PORT:-3306}/${DB_DATABASE:-<kosong>}"
echo "  APP_DEBUG: ${APP_DEBUG:-<kosong>}"
echo "=========================================="
