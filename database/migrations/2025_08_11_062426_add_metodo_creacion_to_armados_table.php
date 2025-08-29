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
        Schema::table('armados', function (Blueprint $table) {
            $table->enum('metodo_creacion', ['Manual', 'IA'])->default('Manual')->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('armados', function (Blueprint $table) {
            $table->dropColumn('metodo_creacion');
        });
    }
};
