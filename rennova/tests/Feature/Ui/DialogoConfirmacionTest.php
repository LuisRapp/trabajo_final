<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DialogoConfirmacionTest extends TestCase
{
    public function test_no_quedan_dialogos_nativos_de_confirmacion(): void
    {
        $infractores = collect(File::allFiles(resource_path('views')))
            ->filter(function ($archivo): bool {
                $contenido = File::get($archivo->getPathname());

                return str_contains($contenido, 'wire:confirm')
                    || str_contains($contenido, 'return confirm(');
            })
            ->map(fn ($archivo): string => $archivo->getRelativePathname())
            ->values();

        $this->assertTrue(
            $infractores->isEmpty(),
            'Estas vistas todavia usan el confirm() nativo del navegador: '.$infractores->implode(', ')
        );
    }

    public function test_el_confirmador_expone_la_accion_y_el_mensaje(): void
    {
        $html = Blade::render(
            '<x-ui.confirmador accion="eliminar" :parametros="[7]" mensaje="Eliminar el insumo Aserrin" detalle="El historial se conserva" etiqueta="Eliminar" />'
        );

        $this->assertStringContainsString('confirmar-accion', $html);
        $this->assertStringContainsString('eliminar', $html);
        $this->assertStringContainsString('El historial se conserva', $html);
        $this->assertStringContainsString('Eliminar', $html);
    }

    public function test_el_layout_principal_incluye_el_dialogo_compartido(): void
    {
        $layout = File::get(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('<x-ui.confirmacion />', $layout);
    }
}
