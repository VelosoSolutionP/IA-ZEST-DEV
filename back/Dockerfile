# syntax=docker/dockerfile:1
# ---------- dependências (composer) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --ignore-platform-reqs
COPY src ./src
RUN composer dump-autoload --no-dev --classmap-authoritative

# ---------- runtime (php-fpm) ----------
FROM php:8.4-fpm-alpine AS runtime
RUN apk add --no-cache postgresql-libs \
 && apk add --no-cache --virtual .build postgresql-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql opcache \
 && apk del .build
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data . .
USER www-data
ENV APP_ENV=production
HEALTHCHECK --interval=10s --timeout=3s --retries=5 CMD php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'
