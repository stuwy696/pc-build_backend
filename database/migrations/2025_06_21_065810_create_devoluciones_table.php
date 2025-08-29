<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id('id_devolucion');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta')->onDelete('cascade');
            $table->foreignId('id_componente')->constrained('componentes', 'id_componente')->onDelete('cascade');
            $table->foreignId('id_usuario_empleado')->constrained('usuarios', 'id_usuario')->onDelete('cascade');
            $table->dateTime('fecha_devolucion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('motivo')->nullable();
            $table->integer('cantidad');
            $table->decimal('monto_reembolsado', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};