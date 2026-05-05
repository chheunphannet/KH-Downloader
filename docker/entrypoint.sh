#!/bin/sh
set -e

mkdir -p \
    storage/app/yt-dlp-temp \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint "$@"
