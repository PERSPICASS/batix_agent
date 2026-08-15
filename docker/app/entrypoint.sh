#!/bin/sh
set -e

if [ ! -f /opt/batix-public/build/manifest.json ]; then
  echo "BATIX Growth frontend manifest is missing from the image." >&2
  exit 1
fi

mkdir -p /var/www/html/public
rm -rf /var/www/html/public/build
cp -a /opt/batix-public/. /var/www/html/public/

exec "$@"
