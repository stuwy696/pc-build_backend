<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reglas_compatibilidad', function (Blueprint $table) {
            $table->id('id_regla');
            $table->string('categoria_origen', 50); // CPU, GPU, RAM, etc.
            $table->string('categoria_destino', 50); // CPU, GPU, RAM, etc.
            $table->string('tipo_regla', 50); // 'fabricante', 'socket', 'ddr', 'potencia', etc.
            $table->text('condicion_origen'); // JSON con condiciones para el componente origen
            $table->text('condicion_destino'); // JSON con condiciones para el componente destino
            $table->integer('porcentaje_compatibilidad')->default(95);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['categoria_origen', 'categoria_destino']);
            $table->index(['tipo_regla']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_compatibilidad');
    }
}; 