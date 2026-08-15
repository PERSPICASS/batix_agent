#!/bin/sh
set -e

if [ -d /opt/batix-public ]; then
  mkdir -p /var/www/html/public
  cp -a /opt/batix-public/. /var/www/html/public/
fi

exec "$@"
