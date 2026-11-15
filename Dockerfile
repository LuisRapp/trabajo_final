# syntax=docker/dockerfile:1

# Imagen de produccion de Rennova. Construida desde la raiz del repo:
#   docker build -t rennova .
# La app Laravel vive en rennova/, por eso todas las rutas llevan ese prefijo.

# ---------------------------------------------------------------------------
# Etapa 1: assets. Node compila CSS y JS; el resultado viaja a la etapa 2.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

# Se copian solo los manifiestos para aprovechar la cache de capas:
# npm ci solo se vuelve a ejecutar cuando cambian las dependencias.
COPY rennova/package.json rennova/package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY rennova/resources ./resources
COPY rennova/public ./public
COPY rennova/vite.config.js ./

RUN npm run build

# ---------------------------------------------------------------------------
# Etapa 2: runtime. Imagen final: codigo, vendor y assets ya resueltos.
# ---------------------------------------------------------------------------
# PHP 8.4.27 incluye el parche de php2shell (CVE-2026-109618 / CVE-2026-109619, RCE critico).
# No bajar de esta version ni usar tags flotantes como php:8.4-apache.
# pdo_sqlite y mbstring ya vienen compiladas en la imagen oficial; no reinstalar.
FROM php:8.4.27-apache AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libicu-dev \
        unzip \
        cron \
    && docker-php-ext-install pdo pdo_pgsql intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer solo para el build: las dependencias quedan dentro de la imagen.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html

# El codigo va antes que composer install porque el post-autoload-dump de
# Laravel ejecuta `artisan package:discover`, que necesita el proyecto entero.
COPY rennova/ ./

# Assets compilados en la etapa 1. public/build no se versiona.
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && composer clear-cache \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Configuracion de Apache, cron y entrypoint.
COPY rennova/docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY rennova/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY rennova/docker/laravel-cron /etc/cron.d/laravel-cron
RUN chmod 0644 /etc/cron.d/laravel-cron

# No se usa `crontab`: /etc/cron.d/laravel-cron trae el campo de usuario y cron
# lo lee solo con `service cron start`. Instalarlo con crontab lo mete como
# crontab de root, que no admite ese campo, y termina ejecutando "www-data"
# como comando cada minuto.

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
