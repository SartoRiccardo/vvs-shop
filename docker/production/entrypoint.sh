#!/bin/bash
set -e

APP_DIR="/var/www/bagisto"

log() {
    echo "[entrypoint] $(date '+%Y-%m-%d %H:%M:%S') $*"
}

cd "$APP_DIR"

# Bootstrap .env from example if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Apply runtime env var overrides to .env
[ -n "$APP_KEY" ]       && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL" ]       && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$APP_DEBUG" ]     && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG}|" .env
[ -n "$DB_HOST" ]       && sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
[ -n "$DB_PORT" ]       && sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env
[ -n "$DB_DATABASE" ]   && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
[ -n "$DB_USERNAME" ]   && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
[ -n "$DB_PASSWORD" ]   && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
[ -n "$REDIS_HOST" ]    && sed -i "s|^REDIS_HOST=.*|REDIS_HOST=${REDIS_HOST}|" .env
[ -n "$REDIS_PORT" ]    && sed -i "s|^REDIS_PORT=.*|REDIS_PORT=${REDIS_PORT}|" .env
[ -n "$MAIL_HOST" ]     && sed -i "s|^MAIL_HOST=.*|MAIL_HOST=${MAIL_HOST}|" .env
[ -n "$MAIL_PORT" ]     && sed -i "s|^MAIL_PORT=.*|MAIL_PORT=${MAIL_PORT}|" .env
[ -n "$MAIL_USERNAME" ] && sed -i "s|^MAIL_USERNAME=.*|MAIL_USERNAME=${MAIL_USERNAME}|" .env
[ -n "$MAIL_PASSWORD" ] && sed -i "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=${MAIL_PASSWORD}|" .env

# Generate app key if not set
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    log "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Wait for database
_DB_HOST="${DB_HOST:-mysql}"
_DB_PORT="${DB_PORT:-3306}"
_DB_USER="${DB_USERNAME:-bagisto}"
_DB_PASS="${DB_PASSWORD:-secret}"

log "Waiting for database at ${_DB_HOST}:${_DB_PORT}..."
for i in $(seq 1 60); do
    if php -r "try { new PDO('mysql:host=${_DB_HOST};port=${_DB_PORT}', '${_DB_USER}', '${_DB_PASS}'); } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
        log "Database is ready."
        break
    fi
    if [ "$i" -eq 60 ]; then
        log "ERROR: Database unreachable after 60s"
        exit 1
    fi
    sleep 1
done

# Migrate (as www-data: booting Laravel touches the config cache, so root
# runs would litter storage/framework/cache with root-owned shard dirs that
# php-fpm can't write into later)
log "Running migrations..."
runuser -u www-data -- php artisan migrate --force --no-interaction

mkdir -p storage/app/public

# Cache
log "Caching config/routes/views..."
runuser -u www-data -- php artisan optimize

# Migrate/optimize run as root, so anything they wrote in storage/bootstrap
# cache needs handing back to www-data before php-fpm (running as www-data)
# tries to write to the same files.
chown -R www-data:www-data storage bootstrap/cache
runuser -u www-data -- php artisan storage:link

log "Starting supervisor..."
exec "$@"
