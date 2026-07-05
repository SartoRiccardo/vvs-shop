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
- `mysql` service reads its credentials from `.env.mysql` (not committed — `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`), which must match the `DB_*` values in `.env.api`.
- All services join the external `smallplushies` Docker network.
- The entrypoint (`docker/production/entrypoint.sh`) generates `APP_KEY` if missing, waits for the database, runs migrations, and caches config/routes/views on every start. **It does not seed the database.**

On first deploy against a fresh database, seed manually once:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan db:seed
```

To test the prod image locally without rebuilding, layer `docker-compose.local.yml` (points `app` at an already-built `bagisto:local` image instead of building):

```bash
docker build -f docker/production/Dockerfile -t bagisto:local .
docker compose -f docker-compose.prod.yml -f docker-compose.local.yml up -d
```

## Credits

Built on top of [Bagisto](https://github.com/bagisto/bagisto), an open-source Laravel e-commerce platform, licensed under the [MIT License](https://github.com/bagisto/bagisto/blob/master/LICENSE.txt).
