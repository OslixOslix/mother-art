#!/bin/sh

cd /app

# Отдельный скрипт без set -e: queue:work может завершиться с ошибкой или OOM.
while true; do
    php -d memory_limit=512M artisan queue:work --sleep=3 --tries=3 --timeout=600 --memory=512
    echo "[queue-worker] завершился (код $?), перезапуск через 5 с..." >&2
    sleep 5
done
