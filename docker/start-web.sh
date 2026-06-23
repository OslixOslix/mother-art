#!/bin/sh
set -e

cd /app

# На Railway и в одиночном Docker-контейнере воркер запускается вместе с веб-сервером.
# В docker-compose отключите через RUN_QUEUE_WORKER=false — там отдельный сервис queue.
if [ "${RUN_QUEUE_WORKER:-true}" = "true" ]; then
    /usr/local/bin/start-queue-worker.sh &
fi

if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    php artisan schedule:work &
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
