#!/bin/bash
# Deploy del BACKEND a PRODUCCION (api.cst-colombia.com.co)
# Usage:
#   ./deploy-prod.sh                                  # usa deploy.local.sh
#   ./deploy-prod.sh usuario@host /ruta/cst
#
# Crea deploy.local.sh con:
#   SSH_HOST="u123456789@cst-colombia.com.co"
#   SSH_PATH="/home/u123456789/domains/api.cst-colombia.com.co/cst"
#
# Asume acceso SSH y que el codigo se actualiza con `git pull` en el servidor.
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[ -f "$SCRIPT_DIR/deploy.local.sh" ] && . "$SCRIPT_DIR/deploy.local.sh"

SSH_HOST="${1:-${SSH_HOST:-}}"
SSH_PATH="${2:-${SSH_PATH:-}}"

if [ -z "$SSH_HOST" ] || [ -z "$SSH_PATH" ]; then
  echo "Uso: $0 <usuario@host> <ruta_app>"
  echo "  o crea deploy.local.sh con SSH_HOST y SSH_PATH."
  exit 1
fi

echo "==> Desplegando backend a PRODUCCION en $SSH_HOST:$SSH_PATH"
ssh "$SSH_HOST" "cd '$SSH_PATH' && set -e && \
  git pull --ff-only && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  php artisan db:seed --class='Database\\Seeders\\UnidadesTecnicasSeeder' --force && \
  php artisan db:seed --class='Database\\Seeders\\ClasificacionBiomedicaSeeder' --force && \
  (php artisan storage:link || true) && \
  php artisan optimize"

echo "==> Verificando estado"
ssh "$SSH_HOST" "cd '$SSH_PATH' && php artisan --version && php artisan migrate:status | tail -n 3"

echo "==> OK. ROLLBACK: git -C '$SSH_PATH' checkout <commit> && composer install --no-dev --optimize-autoloader && php artisan optimize"
