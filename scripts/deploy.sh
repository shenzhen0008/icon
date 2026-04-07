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

$PHP_BIN /usr/bin/composer install --no-dev --optimize-autoloader
$PHP_BIN artisan key:generate --force
$PHP_BIN artisan migrate --force
$PHP_BIN artisan livewire:publish --assets

chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www:www storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize

echo "[OK] Deploy bootstrap finished."
