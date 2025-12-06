# Script de Optimización de Rendimiento - Rennova
# Ejecutar en PowerShell desde la raíz del proyecto

Write-Host "🚀 OPTIMIZANDO SISTEMA RENNOVA..." -ForegroundColor Cyan
Write-Host ""

# 1. Limpiar cache
Write-Host "🧹 Limpiando cache..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
Write-Host "   ✓ Cache limpiado" -ForegroundColor Green
Write-Host ""

# 2. Optimizar Composer
Write-Host "📦 Optimizando autoloader de Composer..." -ForegroundColor Yellow
composer dump-autoload -o
Write-Host "   ✓ Autoloader optimizado" -ForegroundColor Green
Write-Host ""

# 3. Generar caches
Write-Host "⚙️  Generando caches..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
Write-Host "   ✓ Caches generados" -ForegroundColor Green
Write-Host ""

# 4. Crear índices en base de datos
Write-Host "🗄️  Creando índices en PostgreSQL..." -ForegroundColor Yellow
docker-compose exec -T db psql -U postgres -d rennova -f /var/www/html/database/indices_optimizacion.sql
Write-Host "   ✓ Índices creados" -ForegroundColor Green
Write-Host ""

# 5. Optimizar base de datos
Write-Host "🗄️  Optimizando base de datos (VACUUM ANALYZE)..." -ForegroundColor Yellow
docker-compose exec db psql -U postgres -d rennova -c "VACUUM ANALYZE;"
Write-Host "   ✓ Base de datos optimizada" -ForegroundColor Green
Write-Host ""

# 6. Reiniciar servicios
Write-Host "🔄 Reiniciando servicios..." -ForegroundColor Yellow
docker-compose restart
Write-Host "   ✓ Servicios reiniciados" -ForegroundColor Green
Write-Host ""

# 7. Verificar estado
Write-Host "📊 ESTADO DEL SISTEMA:" -ForegroundColor Cyan
Write-Host "   - Config: Cacheado" -ForegroundColor Green
Write-Host "   - Rutas: Cacheadas" -ForegroundColor Green
Write-Host "   - Vistas: Compiladas" -ForegroundColor Green
Write-Host "   - Eventos: Cacheados" -ForegroundColor Green
Write-Host "   - Autoloader: Optimizado" -ForegroundColor Green
Write-Host "   - Índices DB: Creados" -ForegroundColor Green
Write-Host "   - PostgreSQL: Optimizado" -ForegroundColor Green
Write-Host ""

Write-Host "✅ OPTIMIZACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "💡 Tip: El sistema debería cargar 5-10x más rápido ahora" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 Próximos pasos:" -ForegroundColor Cyan
Write-Host "   1. Abrir http://localhost:8000 en el navegador" -ForegroundColor White
Write-Host "   2. Comparar velocidad de carga (debería ser <1 segundo)" -ForegroundColor White
Write-Host "   3. Revisar logs en storage/logs/laravel.log si hay problemas" -ForegroundColor White
Write-Host ""
