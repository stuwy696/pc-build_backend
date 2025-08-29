<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_armado', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_armado')->constrained('armados', 'id_armado')->onDelete('cascade');
            $table->foreignId('id_componente')->constrained('componentes', 'id_componente')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_armado');
    }
};