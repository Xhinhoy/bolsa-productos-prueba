#!/bin/sh
set -e
cd /var/www/html

# APP_KEY persistida en el volumen: no se commitea y sobrevive reinicios
KEYFILE=storage/app/.appkey
[ -s "$KEYFILE" ] || php -r "echo 'base64:'.base64_encode(random_bytes(32));" > "$KEYFILE"
export APP_KEY="$(cat "$KEYFILE")"

php artisan migrate --force --seed
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
