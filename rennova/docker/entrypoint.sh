#!/bin/bash
set -e

cd /var/www/html

# La configuracion de produccion llega por variables de entorno. El .env solo
# se usa si alguien lo provee explicitamente.
if [ ! -f .env ]; then
	touch .env
fi

# Nunca se pisa una APP_KEY existente: regenerarla invalida las sesiones y deja
# ilegible cualquier dato cifrado con la clave anterior.
# Corre como root porque escribe .env, que www-data no debe poder modificar.
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=base64:.+' .env; then
	echo "Generando APP_KEY: no se encontro ninguna configurada."
	php artisan key:generate --force
	echo "AVISO: copia la APP_KEY generada a .env.production. Si no, el proximo" >&2
	echo "redeploy genera otra y se invalidan las sesiones y los datos cifrados." >&2
fi

# Todo comando de la aplicacion corre como www-data, el mismo usuario con el
# que escribe Apache. Si corriera como root, los archivos que cree en storage/
# quedarian de root y www-data no podria escribir en ellos.
como_www_data() {
	gosu www-data "$@"
}

# El symlink se crea como root: public/ no es escribible por www-data y no
# necesita serlo. Es un symlink, no un archivo: Apache lo lee igual.
php artisan storage:link || true

# Espera acotada a Postgres. Si no responde, el contenedor falla en vez de
# arrancar contra una base inexistente.
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
	intentos=0
	until php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "db", (int) (getenv("DB_PORT") ?: 5432)) ? 0 : 1);'; do
		intentos=$((intentos + 1))
		if [ "$intentos" -ge 60 ]; then
			echo "ERROR: Postgres no responde en ${DB_HOST:-db}:${DB_PORT:-5432}." >&2
			exit 1
		fi
		sleep 1
	done
fi

# Sin || true: si las migraciones fallan, el despliegue esta mal y el
# contenedor no debe quedar arriba como si estuviera sano.
como_www_data php artisan migrate --force

service cron start

# Worker de cola en background: procesa jobs encolados (asignacion automatica,
# correos, etc.) que de otro modo se acumulan en la tabla `jobs`. El redirect
# va dentro del gosu para que el log quede a nombre de www-data.
nohup gosu www-data sh -c 'php artisan queue:work --sleep=3 --tries=3 --timeout=90 >> storage/logs/queue-worker.log 2>&1' &

exec apache2-foreground
