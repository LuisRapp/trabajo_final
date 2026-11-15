# syntax=docker/dockerfile:1

# Imagen de produccion de Rennova. Construida desde la raiz del repo:
#   docker build -t rennova .
# La app Laravel vive en rennova/, por eso todas las rutas llevan ese prefijo.

# ---------------------------------------------------------------------------
# Etapa base: PHP con las extensiones del proyecto.
# La comparten la etapa de dependencias y la imagen final, asi la instalacion
# se hace una sola vez y BuildKit reutiliza la capa.
# ---------------------------------------------------------------------------
# PHP 8.4.27 incluye el parche de php2shell (CVE-2026-109618 / CVE-2026-109619, RCE critico).
# No bajar de esta version ni usar tags flotantes como php:8.4-apache.
# pdo, ctype, dom, fileinfo, filter, hash, iconv, json, libxml, mbstring, openssl,
# pcre, session y tokenizer ya vienen compiladas: no reinstalar.
# gosu permite correr los comandos de artisan como www-data. Si corren como
# root, cualquier archivo que creen en storage/ (por ejemplo laravel.log) queda
# de root y despues www-data no puede escribir ahi.
FROM php:8.4.27-apache AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libicu-dev \
        unzip \
    && docker-php-ext-install pdo_pgsql intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Etapa 1: dependencias PHP.
# Se resuelven una sola vez y las consumen la etapa de assets y la imagen
# final: resources/css/app.css importa vendor/livewire/flux/dist/flux.css y
# usa @source sobre los stubs de Flux y las vistas de paginacion de Laravel.
# ---------------------------------------------------------------------------
FROM base AS vendor

WORKDIR /app

COPY rennova/composer.json rennova/composer.lock ./

# --no-scripts: el post-autoload-dump de Laravel ejecuta `artisan
# package:discover`, que necesita la aplicacion completa. Se corre en la etapa
# de runtime, donde el codigo ya esta.
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ---------------------------------------------------------------------------
# Etapa 2: assets. Node compila CSS y JS; el resultado viaja a la etapa 3.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

# Solo los manifiestos, para que npm ci se rehaga unicamente cuando cambian
# las dependencias.
COPY rennova/package.json rennova/package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY rennova/resources ./resources
COPY rennova/public ./public
COPY rennova/vite.config.js ./

# Sin vendor, el build falla resolviendo el CSS de Flux y los @source.
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# ---------------------------------------------------------------------------
# Etapa 3: runtime. Imagen final: codigo, vendor y assets ya resueltos.
# ---------------------------------------------------------------------------
FROM base AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends cron gosu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

# El codigo se copia antes de tocar vendor: el .dockerignore excluye
# rennova/vendor, asi que no pisa el que viene de la etapa 1.
COPY rennova/ ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# dump-autoload dispara el post-autoload-dump de Laravel (package:discover),
# que ahora si encuentra el proyecto.
RUN composer dump-autoload --no-dev --optimize \
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
