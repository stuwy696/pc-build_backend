<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armados', function (Blueprint $table) {
            $table->id('id_armado');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->onDelete('set null');
            $table->dateTime('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('presupuesto', 10, 2);
            $table->enum('estado', ['Cotizacion', 'Completado', 'Cancelado'])->default('Cotizacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armados');
    }
};