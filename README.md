# SmallPlushies

An e-commerce shop built on [Bagisto](https://bagisto.com).

## Local Development

Requirements: PHP 8.3+, Composer, Node.js, and Docker (for MySQL/Redis).

```bash
composer install

# Start MySQL + Redis (dev containers, host ports 3308/6380)
docker compose -f docker-compose.dev.yml up -d

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan db:seed   # seeds locales, channels, currencies, etc. — manual step, not automatic

php artisan serve
```

Frontend assets (Admin/Shop each have their own Vite build):

```bash
cd packages/Webkul/Admin && npm install && npm run dev
cd packages/Webkul/Shop && npm install && npm run dev
```

See `CLAUDE.md` for testing, code style, and translation commands.

## Production Deployment

The production image is built from `docker/production/Dockerfile` (Nginx + PHP-FPM 8.3, supervised).

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Configuration:
- `app` service reads its Laravel env from `.env.api` (not committed — copy from `.env.example` and fill in production values).
  - **`APP_KEY` must be a real, static `base64:...` value** (generate one with `php artisan key:generate --show`). Leaving it blank breaks the app: `env_file` sets `APP_KEY` as an actual container environment variable, which takes precedence over whatever the entrypoint's `key:generate` writes into its internal `.env` — so a blank `APP_KEY` here always wins and Laravel throws `MissingAppKeyException`, no matter what the entrypoint does.
- `mysql` service reads its credentials from `.env.mysql` (not committed — `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`), which must match the `DB_*` values in `.env.api`.
- All services join the external `smallplushies` Docker network.
- The entrypoint (`docker/production/entrypoint.sh`) waits for the database, runs migrations, caches config/routes/views, and re-`chown`s `storage`/`bootstrap/cache` back to `www-data` on every start (migrate/optimize run as root and would otherwise leave files php-fpm can't write to). **It does not seed the database.**

On first deploy against a fresh database, seed manually once:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan db:seed
```

If you're re-testing against data seeded after the app already served a broken response, also clear the full-page response cache (separate from `cache:clear` — `spatie/laravel-responsecache` uses its own `file`-store cache and won't be flushed by the default cache clear):

```bash
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker compose -f docker-compose.prod.yml exec app php artisan responsecache:clear
```

To test the prod image locally without rebuilding, layer `docker-compose.local.yml` (points `app` at an already-built `bagisto:local` image instead of building):

```bash
docker build -f docker/production/Dockerfile -t bagisto:local .
docker compose -f docker-compose.prod.yml -f docker-compose.local.yml up -d
```

## Credits

Built on top of [Bagisto](https://github.com/bagisto/bagisto), an open-source Laravel e-commerce platform, licensed under the [MIT License](https://github.com/bagisto/bagisto/blob/master/LICENSE.txt).
