#!/bin/sh
set -e

cd /app

persist_root="${RAILWAY_VOLUME_MOUNT_PATH:-}"

link_persistent_dir() {
    target=$1
    link_path=$2

    mkdir -p "$target"
    mkdir -p "$(dirname "$link_path")"

    if [ -e "$link_path" ] && [ ! -L "$link_path" ]; then
        rm -rf "$link_path"
    fi

    if [ ! -L "$link_path" ]; then
        ln -sfn "$target" "$link_path"
    fi
}

if [ -n "$persist_root" ] && [ -d "$persist_root" ]; then
    link_persistent_dir "$persist_root/app/public" storage/app/public
    link_persistent_dir "$persist_root/app/import-artworks" storage/app/import-artworks

    if [ -n "${DB_DATABASE:-}" ]; then
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
    fi
else
    mkdir -p storage/app/public storage/app/import-artworks
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

mkdir -p public
rm -f public/storage
ln -sfn ../storage/app/public public/storage

if [ -n "$persist_root" ] && [ -d "$persist_root" ]; then
    php artisan migrate --force --ansi
fi

php artisan config:clear --ansi

exec "$@"
