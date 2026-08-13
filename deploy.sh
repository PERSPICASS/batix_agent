#!/usr/bin/env bash
set -euo pipefail

C="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

if [ ! -f .env ]; then
  echo ".env absent. Copiez .env.example vers .env et renseignez les secrets." >&2
  exit 1
fi

$C build --pull
$C up -d redis
$C run --rm app php artisan migrate --force
$C up -d --force-recreate --remove-orphans app nginx queue scheduler
$C exec -T app php artisan optimize:clear
$C exec -T app php artisan optimize
$C restart app nginx queue scheduler
$C exec -T nginx wget -qO- http://127.0.0.1/up >/dev/null
$C ps
