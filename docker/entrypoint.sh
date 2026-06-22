#!/bin/sh
set -e

cd /app

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:clear --ansi

exec "$@"
