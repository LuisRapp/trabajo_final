<<<<<<< ABMs
# 🌾 Sistema Rennova

Sistema integral de gestión agrícola desarrollado con Laravel 11, Livewire 3 y PostgreSQL. Incluye gestión de lotes, mantenimientos, análisis climático inteligente, cálculo de costos y liquidación de pagos.

## 📋 Requisitos Previos

- **Docker** y **Docker Compose** instalados
- **Git** para clonar el repositorio
- Puertos disponibles: `8000` (aplicación) y `5432` (PostgreSQL)

## 🚀 Instalación Rápida con Docker

### 1. Clonar el Repositorio

```bash
git clone https://github.com/LuisRapp/trabajo_final.git
cd trabajo_final
```

### 2. Levantar los Contenedores

```bash
docker-compose up -d
```

Esto creará y ejecutará dos contenedores:
- `rennova_app`: Aplicación Laravel con PHP 8.2 y Apache
- `rennova_db`: Base de datos PostgreSQL 15

### 3. Instalar Dependencias de PHP

```bash
docker-compose exec app composer install
```

### 4. Ejecutar Migraciones y Seeders

```bash
docker-compose exec app php artisan migrate --seed
```

Esto creará:
- Estructura completa de la base de datos
- Usuario administrador por defecto
- Datos de ejemplo (lotes, cultivos, empleados, etc.)

### 5. Generar Clave de Aplicación (si es necesario)

```bash
docker-compose exec app php artisan key:generate
```

### 6. Acceder a la Aplicación

Abre tu navegador en: **http://localhost:8000**

**Credenciales por defecto:**
- Usuario: `admin@rennova.com`
- Contraseña: `password`

## 🛠️ Comandos Útiles

### Gestión de Contenedores

```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs
docker-compose logs -f app

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes (⚠️ borra la BD)
docker-compose down -v

# Reiniciar contenedores
docker-compose restart
```

### Comandos de Laravel dentro del contenedor

```bash
# Acceder al contenedor
docker-compose exec app bash

# Limpiar cachés
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Ejecutar seeders específicos
docker-compose exec app php artisan db:seed --class=NombreDelSeeder

# Ver rutas disponibles
docker-compose exec app php artisan route:list

# Ejecutar tareas programadas manualmente
docker-compose exec app php artisan schedule:run
```

## 📦 Instalación sin Docker (Desarrollo Local)

Si prefieres ejecutar la aplicación localmente sin Docker:

### Requisitos

- PHP 8.2 o superior
- PostgreSQL 15 o superior
- Composer
- Node.js y npm (para assets frontend)

### Pasos

1. **Clonar el repositorio**
```bash
git clone https://github.com/LuisRapp/trabajo_final.git
cd trabajo_final/rennova
```

2. **Configurar base de datos**
   - Crear una base de datos PostgreSQL llamada `rennova`
   - Actualizar el archivo `.env` con tus credenciales:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=rennova
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

3. **Instalar dependencias**
```bash
composer install
npm install
```

4. **Generar clave de aplicación**
```bash
php artisan key:generate
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Compilar assets**
```bash
npm run build
```

7. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

La aplicación estará disponible en: **http://localhost:8000**

## 🎯 Características Principales

### 🌦️ Análisis Climático Inteligente
- Motor de decisiones climáticas con 3 fases (Anticipación, Bloqueo, Reacción)
- Integración con Open-Meteo API
- Predicciones de lluvia para planificación de actividades
- Documentación: [`Documentacion/README_CLIMA_DECISION_SERVICE.md`](./rennova/Documentacion/README_CLIMA_DECISION_SERVICE.md)

### 💰 Gestión de Costos y Pagos
- Cálculo automático de costos operacionales
- Liquidación de pagos a empleados (jornal, destajo, días caídos)
- Reportes de costos por lote y actividad
- Documentación: [`Documentacion/SISTEMA_CALCULO_COSTOS.md`](./rennova/Documentacion/SISTEMA_CALCULO_COSTOS.md)

### 🔧 Sistema de Mantenimientos
- Mantenimientos preventivos y correctivos
- Notificaciones automáticas por email
- Historial completo de mantenimientos
- Documentación: [`Documentacion/SISTEMA_MANTENIMIENTO_DOCS.md`](./rennova/Documentacion/SISTEMA_MANTENIMIENTO_DOCS.md)

### 📊 Parte Diario
- Registro de actividades diarias por lote
- Control de horas trabajadas y empleados
- Seguimiento de insumos y maquinaria
- Validaciones automáticas de disponibilidad

### 📈 Estadísticas y Reportes
- Dashboard con métricas clave
- Reportes de producción por lote
- Análisis de costos y rentabilidad
- Gráficos interactivos

### 🔐 Sistema de Permisos
- Roles y permisos granulares (Spatie Permission)
- Control de acceso por módulo
- Auditoría de acciones de usuarios

## ⚙️ Tareas Programadas

El sistema ejecuta automáticamente las siguientes tareas:

- **Cada 6 horas**: Obtención de pronóstico climático
- **Diariamente a las 6:00 AM**: Verificación de mantenimientos vencidos
- **Diariamente a las 7:00 AM**: Envío de notificaciones de mantenimiento

Documentación: [`Documentacion/TAREAS_PROGRAMADAS_SCHEDULER.md`](./rennova/Documentacion/TAREAS_PROGRAMADAS_SCHEDULER.md)

## 📚 Documentación Completa

Toda la documentación del proyecto se encuentra en [`rennova/Documentacion/`](./rennova/Documentacion/):

- **[INDEX.md](./rennova/Documentacion/INDEX.md)**: Índice general de documentación
- **[RESUMEN_PROYECTO.md](./rennova/Documentacion/RESUMEN_PROYECTO.md)**: Resumen ejecutivo del proyecto
- **[TROUBLESHOOTING.md](./rennova/Documentacion/TROUBLESHOOTING.md)**: Solución de problemas comunes
- **[GUIA_DESPLIEGUE_PRODUCCION.md](./rennova/Documentacion/GUIA_DESPLIEGUE_PRODUCCION.md)**: Guía para despliegue en producción

## 🧪 Ejecutar Pruebas

El proyecto incluye scripts de prueba en la carpeta `rennova/`:

```bash
# Pruebas del motor climático
docker-compose exec app php test_clima_decision_service.php

# Pruebas de cálculo de costos
docker-compose exec app php test_calculo_costos.php

# Pruebas de caja negra
docker-compose exec app php pruebas_caja_negra.php
```

Documentación de pruebas: [`Documentacion/INDICE_PRUEBAS.md`](./rennova/Documentacion/INDICE_PRUEBAS.md)

## 🔍 Estructura del Proyecto

```
trabajo_final/
├── docker-compose.yml          # Configuración de Docker
├── Dockerfile                  # Imagen de la aplicación
├── README.md                   # Este archivo
└── rennova/                    # Aplicación Laravel
    ├── app/                    # Código de la aplicación
    │   ├── Http/              # Controladores y Middleware
    │   ├── Models/            # Modelos Eloquent
    │   ├── Services/          # Lógica de negocio
    │   └── Livewire/          # Componentes Livewire
    ├── database/
    │   ├── migrations/        # Migraciones de BD
    │   └── seeders/           # Datos de prueba
    ├── resources/
    │   └── views/             # Vistas Blade
    ├── routes/                # Definición de rutas
    ├── Documentacion/         # Documentación completa
    └── docker/                # Archivos de configuración Docker
```

## 🐛 Solución de Problemas

### Error de conexión a la base de datos

Si aparece un error de conexión, verifica que:
1. Los contenedores estén ejecutándose: `docker-compose ps`
2. El archivo `.env` tenga `DB_HOST=db` y `DB_PASSWORD=postgres`
3. El contenedor de PostgreSQL esté listo: `docker-compose logs db`

### Puerto 8000 ya en uso

```bash
# Cambiar el puerto en docker-compose.yml
ports:
  - "8001:80"  # Usar 8001 en lugar de 8000
```

### Permisos de archivos en Linux/Mac

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Recrear base de datos desde cero

```bash
docker-compose down -v
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

## 📧 Configuración de Notificaciones Email

Por defecto, el sistema usa Mailtrap para desarrollo. Para configurar otro servicio:

1. Editar `rennova/.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=tu_servidor_smtp
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="tu@email.com"
```

2. Limpiar cachés:
```bash
docker-compose exec app php artisan config:clear
```

Documentación: [`Documentacion/INSTRUCCIONES_NOTIFICACIONES_EMAIL.md`](./rennova/Documentacion/INSTRUCCIONES_NOTIFICACIONES_EMAIL.md)



## 📝 Licencia

Este proyecto es un trabajo final académico.

## 👥 Autor

Luis Rapp - [LuisRapp](https://github.com/LuisRapp)


Rapp Luis Marcelo  
UNaM

🌲 Proyecto: Sistema de Gestión Forestal Rennova

Este proyecto consiste en el desarrollo de un sistema de software destinado a la gestión integral de una empresa forestal.
El sistema permite administrar inventarios forestales, maquinaria, operaciones, personal y finanzas, brindando herramientas para optimizar la planificación, el control y la trazabilidad de las actividades.
Su objetivo principal es mejorar la eficiencia operativa y apoyar la toma de decisiones estratégicas mediante la digitalización y centralización de la información.
>>>>>>> main
