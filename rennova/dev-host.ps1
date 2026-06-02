$ErrorActionPreference = 'Stop'

# Modo dev recomendado:
# - Laravel corre en Windows con `php artisan serve`
# - PostgreSQL corre en Docker (servicio db)
# - El contenedor `app` se detiene para liberar el puerto 8000

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')

Push-Location $repoRoot
try {
    docker compose up -d db
    docker compose stop app
}
finally {
    Pop-Location
}

Set-Location $PSScriptRoot

php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
