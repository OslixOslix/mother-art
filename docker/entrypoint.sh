#!/bin/sh
set -e

cd /app

# Относительный symlink — работает и в контейнере, и на смонтированном томе хоста.
mkdir -p public
ln -sfn ../storage/app/public public/storage
php artisan config:clear --ansi

exec "$@"
