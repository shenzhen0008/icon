#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
PHP_BIN="${PHP_BIN:-/www/server/php/83/bin/php}"
APP_DIR="${APP_DIR:-$DEFAULT_APP_DIR}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/bin/composer}"
ENV_TEMPLATE=".env.production.example"
INSTALL_SCHEDULER_CRON="${INSTALL_SCHEDULER_CRON:-1}"
CRON_TASKS_FILE="${CRON_TASKS_FILE:-scripts/cron.tasks}"
MYSQL_BIN="${MYSQL_BIN:-mysql}"
DB_INIT_ENABLED="${DB_INIT_ENABLED:-0}"
DB_INIT_CHARSET="${DB_INIT_CHARSET:-utf8mb4}"
DB_INIT_COLLATION="${DB_INIT_COLLATION:-utf8mb4_unicode_ci}"
RUN_SEEDER="${RUN_SEEDER:-1}"
WEB_USER="${WEB_USER:-}"
WEB_GROUP="${WEB_GROUP:-}"
CLIENT_ENV_BOOTSTRAP_ON_DEPLOY="${CLIENT_ENV_BOOTSTRAP_ON_DEPLOY:-1}"
CLIENT_ENV_MODE_ON_DEPLOY="${CLIENT_ENV_MODE_ON_DEPLOY:-enforce}"

if [ ! -x "$PHP_BIN" ]; then
  echo "[ERROR] PHP binary not found at: $PHP_BIN"
  exit 1
fi

if [ ! -f "$COMPOSER_BIN" ]; then
  if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="$(command -v composer)"
  else
    echo "[ERROR] composer not found. Set COMPOSER_BIN or install composer."
    exit 1
  fi
fi

if [ ! -d "$APP_DIR" ]; then
  echo "[ERROR] APP_DIR not found: $APP_DIR"
  exit 1
fi

install_cron_entry() {
  local label="$1"
  local entry="$2"
  local current="$3"

  if printf '%s\n' "$current" | grep -Fqx "$entry"; then
    echo "[INFO] $label already exists."
    printf '%s' "$current"
    return
  fi

  local merged
  merged="$(printf '%s\n%s\n' "$current" "$entry" | awk 'NF && !seen[$0]++')"
  printf '%s\n' "$merged" | crontab -
  echo "[INFO] $label installed."
  printf '%s' "$merged"
}

if [ -z "$WEB_USER" ] || [ -z "$WEB_GROUP" ]; then
  if id -u www >/dev/null 2>&1; then
    WEB_USER="${WEB_USER:-www}"
    WEB_GROUP="${WEB_GROUP:-www}"
  elif id -u www-data >/dev/null 2>&1; then
    WEB_USER="${WEB_USER:-www-data}"
    WEB_GROUP="${WEB_GROUP:-www-data}"
  else
    WEB_USER="${WEB_USER:-$(id -un)}"
    WEB_GROUP="${WEB_GROUP:-$(id -gn)}"
    echo "[WARN] Neither user 'www' nor 'www-data' exists. Fallback to $WEB_USER:$WEB_GROUP"
  fi
fi

cd "$APP_DIR"

if [ ! -f .env ]; then
  if [ -f "$ENV_TEMPLATE" ]; then
    cp "$ENV_TEMPLATE" .env
    echo "[INFO] .env created from $ENV_TEMPLATE"
  else
    echo "[ERROR] .env missing and $ENV_TEMPLATE not found"
    exit 1
  fi
fi

get_env_value() {
  local key="$1"
  local file="$2"
  local line

  line="$(grep -E "^${key}=" "$file" | tail -n 1 || true)"
  line="${line#*=}"
  line="${line%\"}"
  line="${line#\"}"
  line="${line%\'}"
  line="${line#\'}"

  printf '%s' "$line"
}

set_env_value() {
  local key="$1"
  local value="$2"
  local file="$3"

  if grep -Eq "^${key}=" "$file"; then
    sed -i.bak -E "s|^${key}=.*$|${key}=${value}|" "$file"
    rm -f "${file}.bak"
  else
    printf '\n%s=%s\n' "$key" "$value" >> "$file"
  fi
}

if [ "$CLIENT_ENV_BOOTSTRAP_ON_DEPLOY" = "1" ]; then
  set_env_value "CLIENT_ENV_ENABLED" "true" ".env"
  set_env_value "CLIENT_ENV_MIDDLEWARE_ENABLED" "true" ".env"
  set_env_value "CLIENT_ENV_DECISION_ENABLED" "true" ".env"
  set_env_value "CLIENT_ENV_DECISION_MODE" "$CLIENT_ENV_MODE_ON_DEPLOY" ".env"
  echo "[INFO] Client env guard bootstrap enabled."
  echo "[INFO] CLIENT_ENV_ENABLED=$(get_env_value "CLIENT_ENV_ENABLED" ".env")"
  echo "[INFO] CLIENT_ENV_MIDDLEWARE_ENABLED=$(get_env_value "CLIENT_ENV_MIDDLEWARE_ENABLED" ".env")"
  echo "[INFO] CLIENT_ENV_DECISION_ENABLED=$(get_env_value "CLIENT_ENV_DECISION_ENABLED" ".env")"
  echo "[INFO] CLIENT_ENV_DECISION_MODE=$(get_env_value "CLIENT_ENV_DECISION_MODE" ".env")"
else
  echo "[INFO] Client env guard bootstrap skipped (CLIENT_ENV_BOOTSTRAP_ON_DEPLOY=$CLIENT_ENV_BOOTSTRAP_ON_DEPLOY)."
fi

if [ ! -f public/build/manifest.json ]; then
  echo "[ERROR] public/build/manifest.json missing. Please upload local build artifacts."
  exit 1
fi

if ! ls public/build/assets/app-*.css >/dev/null 2>&1; then
  echo "[ERROR] app css asset missing under public/build/assets."
  exit 1
fi

if ! ls public/build/assets/app-*.js >/dev/null 2>&1; then
  echo "[ERROR] app js asset missing under public/build/assets."
  exit 1
fi

DB_HOST="$(get_env_value "DB_HOST" ".env")"
DB_PORT="$(get_env_value "DB_PORT" ".env")"
DB_DATABASE="$(get_env_value "DB_DATABASE" ".env")"

if [ "$DB_INIT_ENABLED" = "1" ]; then
  if ! command -v "$MYSQL_BIN" >/dev/null 2>&1; then
    echo "[ERROR] mysql client not found: $MYSQL_BIN"
    exit 1
  fi

  DB_ROOT_USER="${DB_ROOT_USER:-root}"
  DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-}"

  if [ -z "$DB_DATABASE" ]; then
    echo "[ERROR] DB_DATABASE is empty in .env, cannot initialize database."
    exit 1
  fi

  if [ -z "$DB_ROOT_PASSWORD" ]; then
    echo "[ERROR] DB_INIT_ENABLED=1 requires DB_ROOT_PASSWORD."
    exit 1
  fi

  DB_HOST="${DB_HOST:-127.0.0.1}"
  DB_PORT="${DB_PORT:-3306}"

  "$MYSQL_BIN" -h"$DB_HOST" -P"$DB_PORT" -u"$DB_ROOT_USER" -p"$DB_ROOT_PASSWORD" -e \
    "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET $DB_INIT_CHARSET COLLATE $DB_INIT_COLLATION;"

  DB_META="$("$MYSQL_BIN" -N -B -h"$DB_HOST" -P"$DB_PORT" -u"$DB_ROOT_USER" -p"$DB_ROOT_PASSWORD" -e \
    "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$DB_DATABASE';")"
  DB_META_CHARSET="$(printf '%s' "$DB_META" | awk '{print $1}')"
  DB_META_COLLATION="$(printf '%s' "$DB_META" | awk '{print $2}')"

  if [ "$DB_META_CHARSET" != "$DB_INIT_CHARSET" ] || [ "$DB_META_COLLATION" != "$DB_INIT_COLLATION" ]; then
    echo "[ERROR] Database $DB_DATABASE charset/collation mismatch: expected $DB_INIT_CHARSET/$DB_INIT_COLLATION, got $DB_META_CHARSET/$DB_META_COLLATION."
    exit 1
  fi

  echo "[INFO] Database $DB_DATABASE ready with $DB_INIT_CHARSET/$DB_INIT_COLLATION."
fi

$PHP_BIN "$COMPOSER_BIN" install --no-dev --optimize-autoloader
$PHP_BIN artisan key:generate --force
$PHP_BIN artisan migrate --force
if [ "$RUN_SEEDER" = "1" ]; then
  $PHP_BIN artisan db:seed --force
fi
$PHP_BIN artisan livewire:publish --assets

mkdir -p storage/app/public/recharge-receipts
chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
mkdir -p storage/logs
touch storage/logs/laravel.log
chown "$WEB_USER:$WEB_GROUP" storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

if [ -e public/storage ] && [ ! -L public/storage ]; then
  echo "[WARN] public/storage exists and is not a symlink; skip artisan storage:link"
else
  $PHP_BIN artisan storage:link || true
fi

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize

if [ "$INSTALL_SCHEDULER_CRON" = "1" ]; then
  if command -v crontab >/dev/null 2>&1; then
    SCHEDULER_LOG="$APP_DIR/storage/logs/scheduler.log"
    touch "$SCHEDULER_LOG"
    chown "$WEB_USER:$WEB_GROUP" "$SCHEDULER_LOG" || true
    chmod 664 "$SCHEDULER_LOG" || true

    SCHEDULER_CRON="* * * * * cd $APP_DIR && $PHP_BIN artisan schedule:run >> $SCHEDULER_LOG 2>&1"
    CURRENT_CRONTAB="$(crontab -l 2>/dev/null || true)"
    CURRENT_CRONTAB="$(install_cron_entry 'Scheduler crontab entry' "$SCHEDULER_CRON" "$CURRENT_CRONTAB")"

    CRON_TASKS_ABS_PATH="$APP_DIR/$CRON_TASKS_FILE"
    if [ -f "$CRON_TASKS_ABS_PATH" ]; then
      while IFS= read -r raw_line || [ -n "$raw_line" ]; do
        line="$(printf '%s' "$raw_line" | sed 's/^[[:space:]]*//; s/[[:space:]]*$//')"
        if [ -z "$line" ]; then
          continue
        fi
        case "$line" in
          \#*) continue ;;
        esac

        line="${line//\{\{APP_DIR\}\}/$APP_DIR}"
        line="${line//\{\{PHP_BIN\}\}/$PHP_BIN}"
        CURRENT_CRONTAB="$(install_cron_entry "Cron task from $CRON_TASKS_FILE" "$line" "$CURRENT_CRONTAB")"
      done < "$CRON_TASKS_ABS_PATH"
    else
      echo "[INFO] No extra cron tasks file found at $CRON_TASKS_ABS_PATH, skipped."
    fi
  else
    echo "[WARN] crontab command not found; skip scheduler crontab setup."
  fi
else
  echo "[INFO] Scheduler crontab setup skipped (INSTALL_SCHEDULER_CRON=$INSTALL_SCHEDULER_CRON)."
fi

echo "[OK] Deploy bootstrap finished."
