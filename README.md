# Rennova

Sistema de gestión para operaciones forestales: lotes, maquinaria, personal,
partes diarios, inventario, mantenimiento preventivo con decisiones por clima e
indicadores financieros.

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.4 / Laravel 12 |
| Frontend | Livewire 3 + Flux UI + Tailwind CSS 4 (compilado con Vite) |
| Base de datos | PostgreSQL |
| Empaquetado | Docker (imagen multi-stage) |

## Estructura del repositorio

```
.
├── rennova/                 # Aplicación Laravel
│   ├── app/                 # Componentes Livewire, servicios, modelos
│   ├── database/            # Migraciones y seeders
│   ├── resources/views/     # Vistas Blade / Livewire
│   ├── routes/              # Rutas web y scheduler (routes/console.php)
│   └── tests/               # Suite Pest
├── Dockerfile               # Imagen de producción (multi-stage)
├── docker-compose.prod.yml  # Producción: app + PostgreSQL
├── docker-compose.yml       # Desarrollo
├── .env.production.example  # Plantilla de variables para producción
├── 1-Requisitos.docx.md     # Documento de cátedra
├── 2.-Análisis.docx.md      # Documento de cátedra
└── 3.-Diseño.doc.md         # Documento de cátedra
```

## Módulos

- **Dashboard**: indicadores del día, selector de lote, clima y proyección.
- **Lotes**: alta y edición, ficha, asignación de recursos y ciclo de vida.
- **Partes diarios y cargas**: producción diaria, tareas, pesajes y costos.
- **Ventas**: facturación de cargas pendientes, comprobantes y bajas.
- **Inventario**: insumos, lotes FIFO, movimientos, stock y propuestas de compra.
- **Maquinaria**: flota, tipos, históricos de costos y odómetro por toneladas.
- **Mantenimiento**: tipos, kits, órdenes preventivas/correctivas, aprobación y cierre.
- **Personal y pagos**: empleados, roles laborales, jornales, adelantos y liquidaciones.
- **Clientes y proveedores**: catálogos y cuentas.
- **Reportes y estadísticas**: costo/tn, punto de equilibrio, tn/día, tendencia y PDFs.
- **Usuarios y permisos**: roles, permisos por módulo y auditoría.

## Los tres procesos automatizados

### 1. Mantenimiento preventivo por umbral

Cada carga suma toneladas a la máquina. Cuando supera su `umbral_toneladas`, el
comando `mantenimiento:check-umbrales` genera una orden preventiva, la programa
en un día de lluvia de la ventana de 72 h (o al día siguiente si no hay lluvia),
verifica el stock del kit y, si falta, genera una propuesta de compra. Avisa a
los usuarios configurados.

### 2. Decisiones climáticas por lote

`clima:decisiones` consulta el pronóstico y el histórico de Open-Meteo por lote,
clasifica cada día como operativo o no operativo (lluvia, barro, saturación) y
calcula el déficit de producción. Según el resultado recomienda una estrategia:
anticipar, priorizar o reprogramar. Persiste la decisión por lote y notifica a
los usuarios de recordatorio.

### 3. Propuestas de asignación automática

Al cambiar el estado de un lote o guardar su planificación, se encolan propuestas
de empleados, maquinarias e insumos basadas en el historial de desempeño. El
usuario las revisa y confirma; al confirmar, el lote pasa a `en_proceso` con los
recursos asignados.

## Cómo correr el proyecto

### Opción A: local (PHP + PostgreSQL)

Requisitos: PHP 8.4, Composer, Node.js 20+, PostgreSQL.

```bash
cd rennova
composer install
cp .env.example .env
# Completar DB_* en .env con una base PostgreSQL
php artisan key:generate

createdb rennova
php artisan migrate --force

npm install
npm run build

# Roles y permisos (una vez)
php artisan db:seed --class=RolesAndPermissionsSeeder

# Catálogo de mantenimiento (una vez)
php artisan db:seed --class=CatalogoMantenimientoSeeder

# Administrador (elegir una password)
ADMIN_PASSWORD='una-clave-larga-y-unica' php artisan db:seed --class=AdminUserSeeder

php artisan serve
```

Entrar en `http://127.0.0.1:8000` con `admin@rennova.com` y la password elegida.

### Opción B: Docker (recomendada)

```bash
cp .env.production.example .env.production
# Completar APP_KEY, DB_* y ADMIN_EMAIL/ADMIN_PASSWORD
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

En el arranque, el entrypoint:

- corre las migraciones,
- crea roles y permisos (solo si la base es nueva),
- crea el catálogo de mantenimiento (Preventivo/Correctivo),
- y si `ADMIN_PASSWORD` está definida, crea el administrador con ese email y esa
  password.

No hay usuario demo ni contraseña fija. Entrar en `http://localhost:8080` con
`admin@rennova.com` y la password elegida.

Si no se definió `ADMIN_PASSWORD`, el administrador se puede crear a mano:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production exec -T app \
  php artisan db:seed --class=AdminUserSeeder --force
```

El scheduler y el worker de cola arrancan con el contenedor. El detalle de
variables, backup y rollback está en
[`rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md`](rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md).

## Tests

```bash
cd rennova
php artisan test
vendor/bin/pint --test
```

La suite corre contra SQLite en memoria (configurada en `phpunit.xml`).

## Documentación

- `1-Requisitos.docx.md`, `2.-Análisis.docx.md`, `3.-Diseño.doc.md` — documentos de cátedra.
- `rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md` — despliegue, backup y rollback.
- `rennova/Documentacion/TAREAS_PROGRAMADAS_SCHEDULER.md` — detalle del scheduler.
- `rennova/Documentacion/DEUDA_TECNICA.md` — deuda técnica conocida.
