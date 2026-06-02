<?php

namespace Database\Seeders;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInventario;
use App\Models\Maquinaria;
use App\Models\Mantenimiento;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;
use App\Models\Proveedor;
use App\Models\TipoMaquinaria;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoDaySeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_ES');

        Schema::disableForeignKeyConstraints();
        $tables = [
            'venta_cargas',
            'carga_maquinaria',
            'carga_empleado',
            'parte_diario_empleado',
            'lote_maquinaria',
            'lote_empleado',
            'maquinaria_parte_diarios',
            'movimiento_stocks',
            'lotes_inventario',
            'mantenimientos',
            'cargas',
            'parte_diarios',
            'maquinarias',
            'tipo_maquinarias',
            'lotes',
            'insumos',
            'unidad_medidas',
            'proveedors',
            'clientes',
            'empleados',
            'ventas',
            'usuarios',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();

        // Setup inicial base
        Usuario::factory()->create([
            'nombre' => 'Demo',
            'apellido' => 'Admin',
            'email' => 'demo@example.com',
            'password' => bcrypt('demo1234'),
            'activo' => true,
        ]);
        Usuario::factory()->count(2)->create();

        $clientes = Cliente::factory()->count(3)->create();
        $proveedores = Proveedor::factory()->count(3)->create();

        $unidadLitro = UnidadMedida::factory()->create(['nombre' => 'Litro', 'abreviatura' => 'lt']);
        $unidadUnidad = UnidadMedida::factory()->create(['nombre' => 'Unidad', 'abreviatura' => 'u']);

        // Tipos de maquinaria base
        $tipoSkidder = TipoMaquinaria::factory()->create(['nombre' => 'Skidder forestal', 'umbral_toneladas' => 6000]);
        $tipoGrua = TipoMaquinaria::factory()->create(['nombre' => 'Grúa de carga', 'umbral_toneladas' => 10000]);
        $tipoForwarder = TipoMaquinaria::factory()->create(['nombre' => 'Forwarder pesado', 'umbral_toneladas' => 8000]);

        // Insumos clave para la demo
        $gasoil = Insumo::factory()->create([
            'nombre' => 'Gasoil Grado 3',
            'descripcion' => 'Combustible para toda la flota',
            'id_unidad_medida' => $unidadLitro->id_unidad_medida,
            'id_proveedor' => $proveedores->first()->id_proveedor,
            'costo_unitario' => 1200,
        ]);
        Insumo::factory()->create([
            'nombre' => 'Aceite hidráulico',
            'descripcion' => 'Lubricación de sistemas',
            'id_unidad_medida' => $unidadLitro->id_unidad_medida,
            'id_proveedor' => $proveedores->get(1)->id_proveedor ?? $proveedores->first()->id_proveedor,
            'costo_unitario' => 950,
        ]);
        Insumo::factory()->create([
            'nombre' => 'Kit de repuestos',
            'descripcion' => 'Filtros y correas de recambio',
            'id_unidad_medida' => $unidadUnidad->id_unidad_medida,
            'id_proveedor' => $proveedores->get(2)->id_proveedor ?? $proveedores->first()->id_proveedor,
            'costo_unitario' => 45000,
        ]);

        // Stock inicial de gasoil para cubrir todos los escenarios
        $stockGasoil = LoteInventario::create([
            'id_insumo' => $gasoil->id_insumo,
            'id_proveedor' => $proveedores->first()->id_proveedor,
            'cantidad_inicial' => 200000, // litros
            'cantidad_disponible' => 200000,
            'precio_unitario' => 1200,
            'costo_total' => 200000 * 1200,
            'fecha_compra' => Carbon::now()->subDays(40),
            'numero_factura' => 'FAC-' . $faker->numerify('###-#####'),
            'tipo_movimiento' => 'compra',
            'observaciones' => 'Stock inicial para demo climática',
            'agotado' => false,
        ]);

        MovimientoStock::create([
            'id_insumo' => $gasoil->id_insumo,
            'tipo' => 'entrada',
            'cantidad' => 200000,
            'fecha' => Carbon::now()->subDays(40)->format('Y-m-d'),
            'motivo' => 'Stock inicial DemoDay',
            'precio_unitario' => 1200,
            'id_lote_inventario' => $stockGasoil->id_lote_inventario,
            'costo_total_movimiento' => 200000 * 1200,
        ]);

        // ===== Escenario A: Alerta Financiera (bajo rendimiento)
        $loteA = Lote::create([
            'propietario' => 'Lote Bajo Rendimiento',
            'condicion_compra' => 'propio',
            'estado' => 'activo',
            'ubicacion' => 'Corrientes - Sector Norte',
            'especie' => 'Pino elliottii',
            'superficie' => 120.5,
            'latitud' => -27.48120000,
            'longitud' => -55.91020000,
        ]);

        $maquinariaA = Maquinaria::factory()->create([
            'id_tipo_maquinaria' => $tipoSkidder->id_tipo_maquinaria,
            'modelo' => 'Skidder BR-40',
            'estado' => 'activo',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => Carbon::now()->subYears(2),
            'toneladas_acumuladas' => 3200,
            'umbral_toneladas' => 6000,
        ]);
        $loteA->maquinarias()->syncWithoutDetaching([$maquinariaA->id_maquinaria]);
        $this->generarHistoria($loteA, 30, 35, 45, $gasoil, $stockGasoil, $maquinariaA);

        // ===== Escenario B: Alerta Mantenimiento (85% de desgaste)
        $loteB = Lote::create([
            'propietario' => 'Lote Mecánico',
            'condicion_compra' => 'propio',
            'estado' => 'activo',
            'ubicacion' => 'Entre Ríos - Zona Centro',
            'especie' => 'Eucalipto grandis',
            'superficie' => 95.3,
            'latitud' => -32.06000000,
            'longitud' => -60.64000000,
        ]);

        $maquinariaB = Maquinaria::factory()->create([
            'id_tipo_maquinaria' => $tipoGrua->id_tipo_maquinaria,
            'modelo' => 'Grúa hidráulica K905',
            'estado' => 'activo',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => Carbon::now()->subYears(3),
            'toneladas_acumuladas' => 8500, // 85% del umbral configurado abajo
            'umbral_toneladas' => 10000,
        ]);
        $loteB->maquinarias()->syncWithoutDetaching([$maquinariaB->id_maquinaria]);
        $this->generarHistoria($loteB, 30, 70, 95, $gasoil, $stockGasoil, $maquinariaB);

        // ===== Escenario C: Alerta Climática (alta producción)
        $loteC = Lote::create([
            'propietario' => 'Lote Misiones Real',
            'condicion_compra' => 'contrato_mixto',
            'estado' => 'activo',
            'ubicacion' => 'Misiones - Bertoni',
            'especie' => 'Pino taeda',
            'superficie' => 180.0,
            'latitud' => -25.69513900,
            'longitud' => -54.43638900,
        ]);

        $maquinariaC = Maquinaria::factory()->create([
            'id_tipo_maquinaria' => $tipoForwarder->id_tipo_maquinaria,
            'modelo' => 'Forwarder 1210E',
            'estado' => 'activo',
            'es_alquilada' => true,
            'fecha_inicio_actividades' => Carbon::now()->subYears(1),
            'toneladas_acumuladas' => 4100,
            'umbral_toneladas' => 8000,
        ]);
        $loteC->maquinarias()->syncWithoutDetaching([$maquinariaC->id_maquinaria]);
        $this->generarHistoria($loteC, 30, 90, 120, $gasoil, $stockGasoil, $maquinariaC);
    }

    /**
     * Genera historial de producción, consumos y gastos para un lote.
     */
    private function generarHistoria(Lote $lote, int $dias, float $minTn, float $maxTn, Insumo $gasoil, LoteInventario $stockGasoil, ?Maquinaria $maquinaria = null): void
    {
        $faker = fake('es_ES');
        $fechaBase = Carbon::today();

        for ($i = 0; $i < $dias; $i++) {
            $fecha = $fechaBase->copy()->subDays($i);
            $toneladas = $faker->randomFloat(2, $minTn, $maxTn);
            $pesoNeto = round($toneladas * 1000, 2); // en kg
            $tara = $faker->randomFloat(2, 7000, 12000);
            $pesoBruto = $pesoNeto + $tara;

            $parte = ParteDiario::factory()->create([
                'id_lote' => $lote->id_lote,
                'fecha' => $fecha->format('Y-m-d'),
                'es_dia_caido' => false,
                'observaciones' => $faker->optional(0.3)->sentence(8),
            ]);

            $carga = Carga::factory()->create([
                'id_lote' => $lote->id_lote,
                'id_parte_diario' => $parte->id_parte_diario,
                'ticket' => 'TK-' . $faker->numerify('#####'),
                'peso_bruto' => $pesoBruto,
                'tara' => $tara,
                'peso_neto' => $pesoNeto,
                'destino' => $faker->randomElement(['Aserradero Sur', 'Planta de chips', 'Depósito central', 'Cliente final']),
                'fecha_carga' => $fecha->format('Y-m-d'),
            ]);

            if ($maquinaria) {
                $carga->maquinarias()->syncWithoutDetaching([$maquinaria->id_maquinaria]);
            }

            // Consumo de gasoil asociado al parte
            $litros = $faker->randomFloat(2, $toneladas * 2.5, $toneladas * 3.5);
            $precioUnitario = $stockGasoil->precio_unitario ?? ($gasoil->costo_unitario ?? 1);

            MovimientoStock::create([
                'id_insumo' => $gasoil->id_insumo,
                'tipo' => 'salida',
                'cantidad' => $litros,
                'fecha' => $fecha->format('Y-m-d'),
                'motivo' => 'Parte Diario #' . $parte->id_parte_diario . ' - Gasoil',
                'precio_unitario' => $precioUnitario,
                'id_lote_inventario' => $stockGasoil->id_lote_inventario,
                'costo_total_movimiento' => round($litros * $precioUnitario, 2),
            ]);

            $stockGasoil->cantidad_disponible = max(0, $stockGasoil->cantidad_disponible - $litros);
            $stockGasoil->agotado = $stockGasoil->cantidad_disponible <= 0;
            $stockGasoil->save();

            // Gasto operativo (mantenimiento) vinculado a la maquinaria usada
            $costoMantenimiento = $maquinaria
                ? $faker->randomFloat(2, 25000, 65000)
                : $faker->randomFloat(2, 15000, 30000);

            if ($maquinaria) {
                Mantenimiento::create([
                    'id_maquinaria' => $maquinaria->id_maquinaria,
                    'id_tipo_mantenimiento' => null,
                    'fecha_inicio' => $fecha->format('Y-m-d'),
                    'fecha_fin' => $fecha->format('Y-m-d'),
                    'costo_total' => $costoMantenimiento,
                    'estado' => 'completado',
                    'toneladas_snapshot' => $maquinaria->toneladas_acumuladas,
                    'costo_mano_obra' => $faker->randomFloat(2, 8000, 18000),
                ]);

                $maquinaria->toneladas_acumuladas = round($maquinaria->toneladas_acumuladas + $toneladas, 2);
                $maquinaria->save();
            }

            $costoInsumos = round($litros * $precioUnitario, 2);
            $costoManoObra = $faker->randomFloat(2, 50000, 90000);
            $costoMaquinaria = $costoMantenimiento;
            $costoTotal = $costoInsumos + $costoManoObra + $costoMaquinaria;
            $costoUnitario = $pesoNeto > 0 ? round($costoTotal / ($pesoNeto / 1000), 2) : null;

            $parte->updateQuietly([
                'costo_mano_obra' => $costoManoObra,
                'costo_insumos' => $costoInsumos,
                'costo_maquinaria' => $costoMaquinaria,
                'costo_total_dia' => $costoTotal,
                'costo_unitario_calculado' => $costoUnitario,
            ]);
        }
    }
}
