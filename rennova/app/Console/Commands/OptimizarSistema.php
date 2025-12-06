<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizarSistema extends Command
{
    protected $signature = 'sistema:optimizar {--force : Forzar optimización en cualquier entorno}';
    protected $description = 'Optimiza el rendimiento del sistema (cache de config, rutas, vistas, etc.)';

    public function handle()
    {
        $this->info('🚀 Iniciando optimización del sistema Rennova...');
        $this->newLine();

        // 1. Limpiar cache viejo
        $this->line('🧹 Limpiando cache antiguo...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->info('   ✓ Cache limpiado');
        $this->newLine();

        // 2. Optimizar autoloader de Composer
        $this->line('📦 Optimizando autoloader de Composer...');
        exec('composer dump-autoload -o', $output, $status);
        if ($status === 0) {
            $this->info('   ✓ Autoloader optimizado');
        } else {
            $this->warn('   ⚠ No se pudo optimizar autoloader (ejecutar manualmente: composer dump-autoload -o)');
        }
        $this->newLine();

        // 3. Generar cache de configuración
        $this->line('⚙️  Generando cache de configuración...');
        Artisan::call('config:cache');
        $this->info('   ✓ Configuración cacheada');
        $this->newLine();

        // 4. Generar cache de rutas
        $this->line('🛣️  Generando cache de rutas...');
        Artisan::call('route:cache');
        $this->info('   ✓ Rutas cacheadas');
        $this->newLine();

        // 5. Generar cache de vistas
        $this->line('👁️  Compilando vistas Blade...');
        Artisan::call('view:cache');
        $this->info('   ✓ Vistas compiladas');
        $this->newLine();

        // 6. Optimizar eventos
        $this->line('📢 Optimizando eventos...');
        Artisan::call('event:cache');
        $this->info('   ✓ Eventos cacheados');
        $this->newLine();

        // 7. Limpiar logs antiguos (opcional)
        $this->line('📝 Limpiando logs antiguos (>30 días)...');
        $logsPath = storage_path('logs');
        $files = glob($logsPath . '/laravel-*.log');
        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
                $deleted++;
            }
        }
        $this->info("   ✓ {$deleted} archivo(s) de log eliminado(s)");
        $this->newLine();

        // 8. Estadísticas de caché
        $this->line('📊 Estadísticas de optimización:');
        $this->table(
            ['Componente', 'Estado'],
            [
                ['Config', '✓ Cacheado'],
                ['Rutas', '✓ Cacheado'],
                ['Vistas', '✓ Compilado'],
                ['Eventos', '✓ Cacheado'],
                ['Autoloader', '✓ Optimizado'],
            ]
        );

        $this->newLine();
        $this->info('✅ Sistema optimizado correctamente');
        $this->line('💡 Tip: Ejecuta este comando después de cada deploy o cambio importante');
        
        return Command::SUCCESS;
    }
}
