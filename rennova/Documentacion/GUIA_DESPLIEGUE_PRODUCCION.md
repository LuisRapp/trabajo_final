# Guía de despliegue en producción — Rennova

Última actualización: 17 de noviembre de 2026.

Rennova se entrega como **imagen Docker** (PHP 8.4.27 + Apache) junto a un
`docker-compose.prod.yml` que levanta la aplicación y PostgreSQL. Esta guía
describe ese camino; el despliegue nativo (nginx/systemd/crontab) que describía
una versión anterior de este documento **quedó obsoleto** y no se usa.

## Qué se entrega

| Artefacto | Ubicación | Función |
|---|---|---|
| `Dockerfile` | raíz del repo | Imagen multi-stage: compila assets (Node), resuelve `vendor` y arma el runtime (PHP + Apache) |
| `docker-compose.prod.yml` | raíz del repo | Compose de producción: `app` + `db` (PostgreSQL 15) |
| `.env.production.example` | raíz del repo | Plantilla de variables para producción |
| `rennova/docker/entrypoint.sh` | imagen | `key:generate` si falta, `storage:link`, espera a Postgres, `migrate --force`, cron y queue worker |
| `rennova/docker/laravel-cron` | imagen | Ejecuta `php artisan schedule:run` cada minuto |

La imagen fija `php:8.4.27-apache`: esa versión incluye el parche de `php2shell`
(CVE-2026-109618 / CVE-2026-109619, RCE crítico). **No bajar de esa versión.**

## Prerrequisitos

- Docker Engine + Docker Compose v2 (`docker compose`, no `docker-compose`).
- Acceso de lectura al repositorio.

## 1. Clonar y preparar la configuración

```bash
git clone <url-del-repo> rennova
cd rennova
cp .env.production.example .env.production
```

Completar `.env.production` (el archivo **no se versiona**):

```env
APP_NAME=Rennova
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # php artisan key:generate --show  (ver nota)
APP_URL=http://tudominio.com
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=rennova
DB_USERNAME=rennova
DB_PASSWORD=clave-segura
```

**Sobre `APP_KEY`:** si queda vacía, el entrypoint genera una en el primer
arranque y la imprime con un aviso por `stderr`. Hay que copiarla a
`.env.production`, porque el `.env` dentro del contenedor se pierde en el
próximo redeploy y regenerarla **invalida sesiones y datos cifrados**.

## 2. Levantar los servicios

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

El `--env-file` es obligatorio: de ahí salen las variables que consume el
compose (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y las que recibe el
contenedor de la app. La app queda expuesta en `http://localhost:8080` (se
adelanta un reverse proxy o se cambia el puerto en el compose).

Lo que hace el entrypoint en el arranque, en orden:

1. Genera `APP_KEY` si no hay una.
2. `storage:link` (el symlink del storage público).
3. Espera a que Postgres responda (máximo 60 s; si no, el contenedor falla).
4. `php artisan migrate --force` — **sin `|| true`**: si migra mal, el
   despliegue está mal y el contenedor no queda arriba como si estuviera sano.
5. `php artisan db:seed --class=CatalogoMantenimientoSeeder --force` — catálogo
   de referencia (tipos Preventivo y Correctivo) que necesita el proceso
   automático de mantenimiento. Es idempotente.
6. Arranca `cron` (scheduler) y el worker de cola en background.
7. Levanta Apache.

## 3. Verificación post-despliegue

```bash
# Estado de los contenedores
docker compose -f docker-compose.prod.yml ps

# Logs de la app (migraciones, cron, worker)
docker compose -f docker-compose.prod.yml logs -f app
```

Checklist:

- [ ] La app responde en `http://localhost:8080` y el login funciona.
- [ ] Las migraciones corrieron sin error (log de `app` al arrancar).
- [ ] El catálogo de mantenimiento existe:
      `php artisan tinker --execute="echo App\Models\TipoMantenimiento::count();"` → 2.
- [ ] `php artisan schedule:list` muestra las 5 tareas programadas (abajo).
- [ ] El worker de cola está corriendo (ver sección siguiente).

## 4. Tareas programadas (scheduler)

El cron del contenedor ejecuta `schedule:run` cada minuto. Estas son las tareas:

| Comando | Frecuencia | Qué hace |
|---|---|---|
| `clima:real` | Diaria 00:30 | Sincroniza clima real (histórico) |
| `clima:analizar --dias=7` | Diaria 06:00 | Análisis de riesgo climático |
| `clima:decisiones` | Cada 6 horas | Decisiones climáticas por lote |
| `mantenimiento:check-umbrales` | Diaria 06:30 | Genera órdenes de mantenimiento al superarse el umbral de toneladas |
| `mantenimiento:check-programados` | Cada 4 horas | Marca vencidos y notifica |

Para probar una tarea a mano: `docker compose -f docker-compose.prod.yml exec app php artisan mantenimiento:check-umbrales`.

## 5. Worker de cola (obligatorio)

Las propuestas de asignación automática se ejecutan como jobs en cola. **Sin el
worker corriendo, ese proceso no se ejecuta** y los jobs quedan pendientes. El
entrypoint ya lo arranca con:

```
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

El estado de la cola (pendientes y fallidos) se monitorea en la pantalla
**Estado de procesos** (Administración → Estado de procesos). Para ver jobs
fallidos: `docker compose -f docker-compose.prod.yml exec app php artisan queue:failed`.

## 6. Backup

Los datos viven en el volumen `rennova_pgdata` (Postgres) y `rennova_storage`
(archivos de la app). El backup se hace con `pg_dump` desde el contenedor de la
base:

```bash
docker compose -f docker-compose.prod.yml exec -T db \
  pg_dump -U rennova rennova | gzip > rennova_$(date +%Y%m%d).sql.gz
```

Restaurar:

```bash
gunzip -c rennova_20260101.sql.gz | docker compose -f docker-compose.prod.yml exec -T db psql -U rennova rennova
```

## 7. Actualizar (redeploy)

```bash
git pull
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

El build regenera la imagen (código + vendor + assets) y el contenedor se
recrea. Las migraciones pendientes corren en el arranque.

## 8. Rollback

```bash
git checkout <commit-anterior>
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

Los datos persisten en los volúmenes; el rollback no los borra.

## 9. Troubleshooting

| Síntoma | Diagnóstico / solución |
|---|---|
| El contenedor no arranca y espera a la base | `docker compose ... logs app`: si dice "Postgres no responde", revisar `DB_HOST=db` y las credenciales de `db` |
| Las tareas no corren | `docker compose ... exec app php artisan schedule:list`; luego `tail -100 storage/logs/laravel.log` |
| Jobs de asignación acumulados | Verificar el worker: `docker compose ... ps` y `logs app \| grep queue` |
| Error `SQLSTATE[08006]` | La app no llega a Postgres: revisar `DB_*` en `.env.production` |
| Cambios de código sin efecto | Rebuild: `up -d --build` (la imagen no se actualiza sola) |

## Seguridad

- `APP_DEBUG=false` en producción (lo fuerza el compose).
- `APP_KEY` estable y guardada en `.env.production`, nunca regenerada en caliente.
- El contenedor corre Apache como `www-data` (no root) gracias a `gosu`.
- El puerto 5432 de Postgres **no** está expuesto al host; solo la app lo alcanza.
- Para HTTPS, poner un reverse proxy (nginx/traefik) delante del puerto 8080 con
  su certificado; el esquema/dominio va en `APP_URL`.
