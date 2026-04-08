#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
Usage:
  # Disable support integrations
  bash scripts/configure-support-env.sh --disable [--env-file .env] [--clear-cache]

  # Configure support integrations
  bash scripts/configure-support-env.sh \
    --tawk-enabled true|false \
    --tawk-property-id <id> \
    --tawk-widget-id <id> \
    --stream-api-key <key> \
    --stream-api-secret <secret> \
    [--stream-channel-type messaging] \
    [--stream-channel-prefix support] \
    [--stream-agent-user-id support_agent_1] \
    [--env-file .env] \
    [--clear-cache]

Notes:
  - This script updates keys in .env (or the file from --env-file).
  - A timestamped backup is always created before changes.
EOF
}

ENV_FILE=".env"
DISABLE_MODE="false"
CLEAR_CACHE="false"

TAWK_ENABLED=""
TAWK_PROPERTY_ID=""
TAWK_WIDGET_ID=""
STREAM_CHAT_API_KEY=""
STREAM_CHAT_API_SECRET=""
STREAM_CHAT_CHANNEL_TYPE="messaging"
STREAM_CHAT_CHANNEL_PREFIX="support"
STREAM_CHAT_AGENT_USER_ID="support_agent_1"

# Default embedded credentials (loaded when corresponding args are not provided).
DEFAULT_TAWK_ENABLED="true"
DEFAULT_TAWK_PROPERTY_ID="69d2b6406c34951c3533e334"
DEFAULT_TAWK_WIDGET_ID="1jlfhfro7"
DEFAULT_STREAM_CHAT_API_KEY="fwbu2stvsbkm"
DEFAULT_STREAM_CHAT_API_SECRET="k2wcwtwqmbfkywbtakkusyb682thkzrcfcek9j827c3m6xs847j89f57ybzmb84d"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --env-file)
      ENV_FILE="${2:-}"
      shift 2
      ;;
    --disable)
      DISABLE_MODE="true"
      shift
      ;;
    --clear-cache)
      CLEAR_CACHE="true"
      shift
      ;;
    --tawk-enabled)
      TAWK_ENABLED="${2:-}"
      shift 2
      ;;
    --tawk-property-id)
      TAWK_PROPERTY_ID="${2:-}"
      shift 2
      ;;
    --tawk-widget-id)
      TAWK_WIDGET_ID="${2:-}"
      shift 2
      ;;
    --stream-api-key)
      STREAM_CHAT_API_KEY="${2:-}"
      shift 2
      ;;
    --stream-api-secret)
      STREAM_CHAT_API_SECRET="${2:-}"
      shift 2
      ;;
    --stream-channel-type)
      STREAM_CHAT_CHANNEL_TYPE="${2:-}"
      shift 2
      ;;
    --stream-channel-prefix)
      STREAM_CHAT_CHANNEL_PREFIX="${2:-}"
      shift 2
      ;;
    --stream-agent-user-id)
      STREAM_CHAT_AGENT_USER_ID="${2:-}"
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "[ERROR] Unknown argument: $1"
      usage
      exit 1
      ;;
  esac
done

if [[ -z "$ENV_FILE" ]]; then
  echo "[ERROR] --env-file cannot be empty."
  exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
  if [[ -f ".env.production.example" ]]; then
    cp ".env.production.example" "$ENV_FILE"
    echo "[INFO] Created $ENV_FILE from .env.production.example"
  elif [[ -f ".env.example" ]]; then
    cp ".env.example" "$ENV_FILE"
    echo "[INFO] Created $ENV_FILE from .env.example"
  else
    echo "[ERROR] $ENV_FILE does not exist, and no template file found."
    exit 1
  fi
fi

if [[ "$DISABLE_MODE" != "true" ]]; then
  [[ -z "$TAWK_ENABLED" ]] && TAWK_ENABLED="$DEFAULT_TAWK_ENABLED"
  [[ -z "$TAWK_PROPERTY_ID" ]] && TAWK_PROPERTY_ID="$DEFAULT_TAWK_PROPERTY_ID"
  [[ -z "$TAWK_WIDGET_ID" ]] && TAWK_WIDGET_ID="$DEFAULT_TAWK_WIDGET_ID"
  [[ -z "$STREAM_CHAT_API_KEY" ]] && STREAM_CHAT_API_KEY="$DEFAULT_STREAM_CHAT_API_KEY"
  [[ -z "$STREAM_CHAT_API_SECRET" ]] && STREAM_CHAT_API_SECRET="$DEFAULT_STREAM_CHAT_API_SECRET"

  if [[ -z "$TAWK_ENABLED" || -z "$STREAM_CHAT_CHANNEL_TYPE" || -z "$STREAM_CHAT_CHANNEL_PREFIX" || -z "$STREAM_CHAT_AGENT_USER_ID" ]]; then
    echo "[ERROR] Missing required args for configure mode."
    usage
    exit 1
  fi
  if [[ "$TAWK_ENABLED" != "true" && "$TAWK_ENABLED" != "false" ]]; then
    echo "[ERROR] --tawk-enabled must be true or false."
    exit 1
  fi
fi

backup_file="${ENV_FILE}.bak.$(date +%Y%m%d%H%M%S)"
cp "$ENV_FILE" "$backup_file"
echo "[INFO] Backup created: $backup_file"

env_quote() {
  local value="$1"
  value="${value//\\/\\\\}"
  value="${value//\"/\\\"}"
  value="${value//\$/\\$}"
  value="${value//\`/\\\`}"
  printf '"%s"' "$value"
}

set_kv() {
  local key="$1"
  local value="$2"
  local quoted
  quoted="$(env_quote "$value")"

  local tmp
  tmp="$(mktemp)"
  awk -v k="$key" -v v="$quoted" '
    BEGIN { replaced = 0 }
    $0 ~ ("^" k "=") {
      if (replaced == 0) {
        print k "=" v
        replaced = 1
      }
      next
    }
    { print }
    END {
      if (replaced == 0) {
        print k "=" v
      }
    }
  ' "$ENV_FILE" > "$tmp"
  mv "$tmp" "$ENV_FILE"
}

if [[ "$DISABLE_MODE" == "true" ]]; then
  TAWK_ENABLED="false"
  TAWK_PROPERTY_ID=""
  TAWK_WIDGET_ID=""
  STREAM_CHAT_API_KEY=""
  STREAM_CHAT_API_SECRET=""
fi

set_kv "TAWK_ENABLED" "$TAWK_ENABLED"
set_kv "TAWK_PROPERTY_ID" "$TAWK_PROPERTY_ID"
set_kv "TAWK_WIDGET_ID" "$TAWK_WIDGET_ID"
set_kv "STREAM_CHAT_API_KEY" "$STREAM_CHAT_API_KEY"
set_kv "STREAM_CHAT_API_SECRET" "$STREAM_CHAT_API_SECRET"
set_kv "STREAM_CHAT_CHANNEL_TYPE" "$STREAM_CHAT_CHANNEL_TYPE"
set_kv "STREAM_CHAT_CHANNEL_PREFIX" "$STREAM_CHAT_CHANNEL_PREFIX"
set_kv "STREAM_CHAT_AGENT_USER_ID" "$STREAM_CHAT_AGENT_USER_ID"

echo "[OK] Support env config updated in $ENV_FILE"

if [[ "$CLEAR_CACHE" == "true" ]]; then
  php_bin="/www/server/php/83/bin/php"
  if [[ -x "$php_bin" && -f "artisan" ]]; then
    "$php_bin" artisan config:clear
    "$php_bin" artisan cache:clear
    "$php_bin" artisan optimize:clear
    echo "[OK] Laravel caches cleared with $php_bin"
  elif command -v php >/dev/null 2>&1 && [[ -f "artisan" ]]; then
    php artisan config:clear
    php artisan cache:clear
    php artisan optimize:clear
    echo "[OK] Laravel caches cleared with system php"
  else
    echo "[WARN] Skip cache clear: php or artisan not found."
  fi
fi
