# Rennova

Sistema de gestión para operaciones forestales: lotes, maquinaria, personal,
partes diarios, inventario, mantenimiento preventivo (con decisiones por clima)
e indicadores financieros (costo/tn unificado, punto de equilibrio, rentabilidad).

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.4 / Laravel 12 |
| Frontend | Livewire 3 + Volt + Flux UI + Tailwind CSS 4 (compilado con Vite) |
| Base de datos | PostgreSQL |
| Empaquetado | Docker (imagen multi-stage) |

## Estructura del repositorio

```
.
├── rennova/                 # Aplicación Laravel (código, tests, migraciones)
├── Dockerfile               # Imagen de producción (multi-stage)
├── docker-compose.prod.yml  # Compose de producción (app + PostgreSQL)
├── docker-compose.yml       # Compose de desarrollo
├── .env.production.example  # Plantilla de variables para producción
└── .github/workflows/       # CI: tests, lint+audit y build Docker
```

Los flujos de CI viven en la raíz porque es el único lugar que GitHub lee.

## Requisitos

- PHP (ver el rango exacto de versiones parcheadas en `rennova/composer.json`; producción fija `8.4.27`, que incluye el parche de `php2shell`)
- Composer
- Node.js >= 20 (para compilar assets)
- PostgreSQL

## Puesta en marcha local

```bash
cd rennova
composer install
cp .env.example .env          # y completar DB_* con una base PostgreSQL
php artisan key:generate

# Crear la base y migrar (ajustar a tu Postgres)
createdb rennova
php artisan migrate --force

# Compilar assets (o `npm run dev` para desarrollo en caliente)
npm install
npm run build

# Levantar
php artisan serve
```

Roles, permisos y administrador:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
ADMIN_PASSWORD='una-clave-larga-y-unica' php artisan db:seed --class=AdminUserSeeder
```

No ejecutes `php artisan db:seed` en producción: `DatabaseSeeder` está pensado
para desarrollo y resetea roles/permisos. Los datos de demostración solo se
crean con `APP_ENV=local` o `testing`.

## Tests

```bash
cd rennova
php artisan test          # suite completa (Pest)
vendor/bin/pint --test    # estilo de código
```

La suite corre contra SQLite en memoria (configurada en `phpunit.xml`).

## Despliegue en producción

Se entrega como imagen Docker. El resumen: copiar `.env.production.example`,
completar las variables y levantar el compose. El detalle (variables,
verificación, scheduler, backup, rollback) está en
[`rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md`](rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md).

### Primer ingreso

La base arranca vacía: no hay ningún usuario hasta que se crea.

1. Copiá la plantilla y completá `APP_KEY`, `DB_*` y el administrador:

```bash
cp .env.production.example .env.production
```

```env
APP_KEY=...
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=rennova
DB_USERNAME=rennova
DB_PASSWORD=una-clave-segura
ADMIN_EMAIL=admin@rennova.com
ADMIN_PASSWORD=una-clave-larga-y-unica
```

2. Levantá:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

En el arranque, el entrypoint:

- corre las migraciones,
- crea roles y permisos (solo si la base es nueva),
- crea el catálogo de mantenimiento (Preventivo/Correctivo),
- y si `ADMIN_PASSWORD` está definida, crea el administrador con ese email y esa password.

No hay usuario demo ni contraseña fija. Después entrás en
`http://localhost:8080` con ese email y esa password.

Si no definiste `ADMIN_PASSWORD`, podés crear el administrador a mano:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production exec -T app \
  php artisan db:seed --class=AdminUserSeeder --force
```

## Documentación

- `rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md` — despliegue en producción.
- `rennova/Documentacion/DEUDA_TECNICA.md` — deuda técnica conocida y su estado.
- `rennova/Documentacion/INDEX.md` — índice de la documentación vigente.
- `rennova/Documentacion/GUIA_LIQUIDACION_PAGOS.md` y `GUIA_RECOMENDACIONES_POR_LOTE.md` — guías de uso.
- `rennova/AGENTS.md` — convenciones de arquitectura y desarrollo.
