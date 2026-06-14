FROM php:8.3-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

RUN apt-get update && apt-get install -y \
        curl \
        imagemagick \
        libfreetype6-dev \
        libgmp-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        supervisor \
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

COPY docker/production/php.ini          /usr/local/etc/php/conf.d/99-production.ini
COPY docker/production/php-fpm.conf     /usr/local/etc/php-fpm.d/www.conf
COPY docker/production/nginx.conf       /etc/nginx/conf.d/bagisto.conf
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/bagisto.conf

RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

RUN mkdir -p /run/php /var/log/supervisor bootstrap/cache \
    && chown -R www-data:www-data /var/www/bagisto \
    && chmod -R 775 storage bootstrap/cache \
    && find storage bootstrap/cache -type d -exec chmod g+s {} +

COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
