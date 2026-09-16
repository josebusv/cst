#!/bin/bash
# Deploy del BACKEND a PREPRODUCCION (apitest.cst-colombia.com.co)
# Usage:
#   ./deploy-preprod.sh                                  # usa deploy.local.sh
#   ./deploy-preprod.sh usuario@host /ruta/cst
#
# Crea deploy.local.sh con:
#   SSH_HOST="u123456789@cst-colombia.com.co"
#   SSH_PATH="/home/u123456789/domains/apitest.cst-colombia.com.co/cst"
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

echo "==> Desplegando backend en $SSH_HOST:$SSH_PATH"
ssh "$SSH_HOST" "cd '$SSH_PATH' && set -e && \
  git pull --ff-only && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  php artisan db:seed --class='Database\\Seeders\\PermissionsDemoSeeder' --force && \
  php artisan optimize"

echo "==> Verificando permisos del rol Administrador"
ssh "$SSH_HOST" "cd '$SSH_PATH' && php artisan tinker --execute=\"echo Spatie\\Permission\\Models\\Role::findByName('Administrador','api')->permissions()->where('name','like','%Hoja De Vida%')->pluck('name');\""

echo "==> OK. ROLLBACK: git -C '$SSH_PATH' checkout <commit> && php artisan optimize"
