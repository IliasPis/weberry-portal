# syntax=docker/dockerfile:1

# --- Frontend build ------------------------------------------------------
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.ts tsconfig.json components.json ./
COPY public ./public
RUN npm run build

# --- PHP dependencies ------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY database ./database
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# --- Runtime ------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        libpq \
        libzip \
        icu-libs \
        oniguruma \
        libpng \
        libjpeg-turbo \
        freetype \
        shadow \
    && apk add --no-cache --virtual .build-deps \
        postgresql-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        pcntl \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && apk del .build-deps

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN addgroup -g 1000 weberry \
    && adduser -G weberry -u 1000 -D weberry \
    && chown -R weberry:weberry /var/www/html \
    && chmod -R 775 storage bootstrap/cache

USER weberry

EXPOSE 9000

CMD ["php-fpm"]
