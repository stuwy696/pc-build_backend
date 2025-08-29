<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id('id_componente');
            $table->string('nombre', 100);
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->enum('categoria', ['CPU', 'GPU', 'RAM', 'Motherboard', 'Storage', 'PSU', 'Case']);
            $table->decimal('precio', 10, 2);
            $table->integer('stock');
            $table->enum('gama', ['Media', 'Baja']);
            $table->text('especificaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};