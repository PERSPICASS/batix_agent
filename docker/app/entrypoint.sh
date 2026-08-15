#!/bin/sh
set -e

if [ -d /opt/batix-public ]; then
  mkdir -p /var/www/html/public
  cp -a /opt/batix-public/. /var/www/html/public/
fi

mkdir -p /var/www/html/storage/app/public
if [ ! -e /var/www/html/public/storage ]; then
  ln -s ../storage/app/public /var/www/html/public/storage
fi

exec "$@"
