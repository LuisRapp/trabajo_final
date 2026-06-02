#!/bin/bash
set -e

cd /var/www/html

if [ ! -f "/var/www/html/.env" ] && [ -f "/var/www/html/.env.example" ]; then
	cp /var/www/html/.env.example /var/www/html/.env
fi

if [ ! -d "/var/www/html/vendor" ]; then
	composer install --no-interaction --prefer-dist --optimize-autoloader
fi

php artisan key:generate --force || true
php artisan storage:link || true

DB_CONNECTION=$(php -r "require 'vendor/autoload.php'; \$dotenv=Dotenv\\Dotenv::createImmutable(__DIR__); \$dotenv->safeLoad(); echo \$_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?? '';" 2>/dev/null || true)
DB_HOST=$(php -r "require 'vendor/autoload.php'; \$dotenv=Dotenv\\Dotenv::createImmutable(__DIR__); \$dotenv->safeLoad(); echo \$_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'db';" 2>/dev/null || echo "db")
DB_PORT=$(php -r "require 'vendor/autoload.php'; \$dotenv=Dotenv\\Dotenv::createImmutable(__DIR__); \$dotenv->safeLoad(); echo \$_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432';" 2>/dev/null || echo "5432")

if [ "$DB_CONNECTION" = "pgsql" ]; then
	echo "Waiting for Postgres at $DB_HOST:$DB_PORT ..."
	for i in $(seq 1 60); do
		php -r "@fsockopen('$DB_HOST', (int)('$DB_PORT')) ? exit(0) : exit(1);" && break
		sleep 1
	done
fi

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan migrate --force || true

service cron start

exec apache2-foreground
