<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado');
            $table->foreignId('id_usuario')->unique()->constrained('usuarios', 'id_usuario')->onDelete('cascade');
            $table->string('cargo', 50)->nullable();
            $table->date('fecha_contratacion');
            $table->decimal('salario', 10, 2)->default(0);
            $table->string('estado', 20)->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};