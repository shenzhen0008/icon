#!/usr/bin/env bash
set -euo pipefail

PHP_BIN="/www/server/php/83/bin/php"
APP_DIR="/www/wwwroot/bitcon.yunqueapp.com"
ENV_TEMPLATE=".env.production.example"

if [ ! -x "$PHP_BIN" ]; then
  echo "[ERROR] PHP 8.3 binary not found at: $PHP_BIN"
  exit 1
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

$PHP_BIN /usr/bin/composer install --no-dev --optimize-autoloader
$PHP_BIN artisan key:generate --force
$PHP_BIN artisan migrate --force
$PHP_BIN artisan livewire:publish --assets

mkdir -p storage/app/public/recharge-receipts
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www:www storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

if [ -e public/storage ] && [ ! -L public/storage ]; then
  echo "[WARN] public/storage exists and is not a symlink; skip artisan storage:link"
else
  $PHP_BIN artisan storage:link || true
fi

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize

echo "[OK] Deploy bootstrap finished."
