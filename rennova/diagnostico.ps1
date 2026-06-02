# diagnostico.ps1
Write-Host "=== DIAGNÓSTICO RENNOVA ===" -ForegroundColor Green

Write-Host "1. Estado de migraciones..." -ForegroundColor Yellow
php artisan migrate:status > migration_status.txt

Write-Host "2. Listado de archivos de migración..." -ForegroundColor Yellow
Get-ChildItem database\migrations\*.php | Select-Object Name > archivos_migracion.txt

Write-Host "3. Buscando funciones SQL en migraciones..." -ForegroundColor Yellow
Get-ChildItem -Path database\migrations -Filter *.php | Select-String -Pattern "FUNCTION|PROCEDURE|DB::statement|DB::raw" > funciones_sql.txt

Write-Host "4. Buscando políticas de stock..." -ForegroundColor Yellow
Get-ChildItem -Path app -Include *.php -Recurse | Select-String -Pattern "stock.*negativo|stock.*insuficiente|permite.*stock" > politicas_stock.txt

Write-Host "5. Buscando lógica de negocio en modelos..." -ForegroundColor Yellow
Get-ChildItem -Path app\Models -Include *.php | Select-String -Pattern "function (calcular|validar|procesar|liquidar|generar|asignar)" > modelos_logica.txt

Write-Host "6. Buscando servicios con muchas responsabilidades..." -ForegroundColor Yellow
Get-ChildItem -Path app\Services -Include *.php -Recurse | ForEach-Object {
    $methods = (Select-String -Path $_.FullName -Pattern "public function" -AllMatches).Matches.Count
    if ($methods -gt 10) {
        Write-Output "$($_.Name): $methods métodos" >> servicios_multimetodos.txt
    }
}

Write-Host "7. Buscando queries directas en Livewire..." -ForegroundColor Yellow
Get-ChildItem -Path app\Http\Livewire -Include *.php -Recurse | Select-String -Pattern "::where|::find|::all|DB::" > livewire_queries.txt

Write-Host "8. Versión de Laravel..." -ForegroundColor Yellow
php artisan --version > version_laravel.txt

Write-Host "¡DIAGNÓSTICO COMPLETADO!" -ForegroundColor Green
Write-Host "Revisá los archivos .txt generados" -ForegroundColor Cyan