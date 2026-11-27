<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula cada produccion (carga) y cada jornal (parte diario) con el
     * recibo que lo pago, por empleado. Sin esto, una liquidacion volvia a
     * contar las mismas toneladas y se pagaban dos veces.
     */
    public function up(): void
    {
        Schema::table('carga_empleado', function (Blueprint $table) {
            $table->foreignId('id_recibo')->nullable()->after('id_empleado')
                ->constrained('recibos', 'id_recibo')->nullOnDelete();
        });

        Schema::table('parte_diario_empleado', function (Blueprint $table) {
            $table->foreignId('id_recibo')->nullable()->after('id_empleado')
                ->constrained('recibos', 'id_recibo')->nullOnDelete();
        });

        $this->marcarPivotesDeRecibosExistentes();
    }

    public function down(): void
    {
        Schema::table('carga_empleado', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_recibo');
        });

        Schema::table('parte_diario_empleado', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_recibo');
        });
    }

    /**
     * Los recibos ya generados no guardaban que produccion pagaban. Se toma el
     * periodo de las observaciones y se marcan los vinculos creados ANTES del
     * recibo, para no marcar cargas cargadas despues como si ya estuvieran pagas.
     */
    private function marcarPivotesDeRecibosExistentes(): void
    {
        $recibos = DB::table('recibos')
            ->whereNull('deleted_at')
            ->whereNotNull('id_empleado')
            ->orderBy('id_recibo')
            ->get(['id_recibo', 'id_empleado', 'observaciones', 'created_at']);

        foreach ($recibos as $recibo) {
            $periodo = $this->periodoDe($recibo->observaciones);

            if (! $periodo) {
                continue;
            }

            [$inicio, $fin] = $periodo;

            DB::table('carga_empleado')
                ->where('id_empleado', $recibo->id_empleado)
                ->whereNull('id_recibo')
                ->whereIn('id_carga', function ($query) use ($inicio, $fin, $recibo) {
                    $query->select('id_carga')
                        ->from('cargas')
                        ->whereBetween('fecha_carga', [$inicio, $fin])
                        ->where('created_at', '<=', $recibo->created_at);
                })
                ->update(['id_recibo' => $recibo->id_recibo]);

            DB::table('parte_diario_empleado')
                ->where('id_empleado', $recibo->id_empleado)
                ->whereNull('id_recibo')
                ->whereIn('id_parte_diario', function ($query) use ($inicio, $fin, $recibo) {
                    $query->select('id_parte_diario')
                        ->from('parte_diarios')
                        ->whereBetween('fecha', [$inicio, $fin])
                        ->where('created_at', '<=', $recibo->created_at);
                })
                ->update(['id_recibo' => $recibo->id_recibo]);
        }
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function periodoDe(?string $observaciones): ?array
    {
        if (! $observaciones || ! preg_match('/(\d{2})\/(\d{2})\/(\d{4})\s+a\s+(\d{2})\/(\d{2})\/(\d{4})/', $observaciones, $m)) {
            return null;
        }

        return ["{$m[3]}-{$m[2]}-{$m[1]}", "{$m[6]}-{$m[5]}-{$m[4]}"];
    }
};
