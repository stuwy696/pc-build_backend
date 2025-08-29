<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compatibilidades', function (Blueprint $table) {
            $table->id('id_compatibilidad');
            $table->foreignId('id_componente1')->constrained('componentes', 'id_componente')->onDelete('cascade');
            $table->foreignId('id_componente2')->constrained('componentes', 'id_componente')->onDelete('cascade');
            $table->decimal('porcentaje_compatibilidad', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibilidades');
    }
};