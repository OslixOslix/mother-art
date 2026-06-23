#!/bin/sh
set -e

cd /app

# Относительный symlink — работает и в контейнере, и на смонтированном томе WSL.
mkdir -p public
rm -f public/storage
ln -sfn ../storage/app/public public/storage
php artisan config:clear --ansi

exec "$@"
