FROM php:8.2-apache

# Instalar extensiones necesarias
RUN apt-get update \
    && apt-get install -y libpq-dev libicu-dev git unzip cron \
    && docker-php-ext-install pdo pdo_pgsql intl

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar archivos de configuración de Apache
COPY ./rennova/docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Copiar el entrypoint para el cron
COPY ./rennova/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Configuración de cron para Laravel
COPY ./rennova/docker/laravel-cron /etc/cron.d/laravel-cron
RUN chmod 0644 /etc/cron.d/laravel-cron
RUN crontab /etc/cron.d/laravel-cron

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
