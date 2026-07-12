##### Stage 1: build PHP extensions + install Composer deps #####
FROM php:8.3-fpm-bookworm AS builder

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libgmp-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        calendar \
        exif \
        gd \
        gmp \
        intl \
        pdo_mysql \
        soap \
        sockets \
        zip

RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Resolve the actual runtime shared-library packages needed by the extensions
# we just compiled, so the final stage only installs those (not the -dev
# headers or build toolchain).
RUN find /usr/local/lib/php/extensions -name '*.so' -exec ldd {} \; \
        | awk '/=>/ { so = $(NF-1); gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
        | sort -u \
        | xargs -rt dpkg-query --search \
        | awk 'sub(":$", "", $1) { print $1 }' \
        | sort -u > /tmp/runtime-deps.txt

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/bagisto

COPY . .

RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && rm -rf /root/.composer/cache

##### Stage 2: slim runtime image #####
FROM php:8.3-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

COPY --from=builder /tmp/runtime-deps.txt /tmp/runtime-deps.txt

RUN apt-get update && apt-get install -y --no-install-recommends \
        $(cat /tmp/runtime-deps.txt) \
        curl \
        imagemagick \
        nginx \
        supervisor \
        unzip \
    && rm -rf /var/lib/apt/lists/* /tmp/runtime-deps.txt

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d     /usr/local/etc/php/conf.d

WORKDIR /var/www/bagisto

COPY --from=builder --chown=www-data:www-data /var/www/bagisto /var/www/bagisto

COPY docker/production/php.ini          /usr/local/etc/php/conf.d/99-production.ini
COPY docker/production/php-fpm.conf     /usr/local/etc/php-fpm.d/www.conf
COPY docker/production/nginx.conf       /etc/nginx/conf.d/bagisto.conf
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/bagisto.conf

RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

RUN mkdir -p /run/php /var/log/supervisor bootstrap/cache \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data /run/php /var/log/supervisor \
    && chmod -R 775 storage bootstrap/cache \
    && find storage bootstrap/cache -type d -exec chmod g+s {} +

COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
