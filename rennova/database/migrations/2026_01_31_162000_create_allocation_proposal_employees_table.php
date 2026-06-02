<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_proposal_employees', function (Blueprint $table) {
            $table->id('id_allocation_proposal_employee');
            $table->unsignedBigInteger('id_allocation_proposal');
            $table->unsignedBigInteger('id_empleado');

            $table->string('rol_sugerido', 30)->nullable();
            $table->decimal('score', 12, 4)->nullable();
            $table->boolean('selected')->default(true);

            $table->timestamps();

            $table->foreign('id_allocation_proposal')
                ->references('id_allocation_proposal')
                ->on('allocation_proposals')
                ->onDelete('cascade');

            $table->foreign('id_empleado')
                ->references('id_empleado')
                ->on('empleados')
                ->onDelete('cascade');

            $table->unique(['id_allocation_proposal', 'id_empleado'], 'uq_alloc_prop_employee');
            $table->index(['id_allocation_proposal', 'selected'], 'idx_alloc_prop_employee_selected');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_proposal_employees');
    }
};
