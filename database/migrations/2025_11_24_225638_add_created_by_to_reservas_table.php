<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Agregar columna para registrar quién creó la reserva
            $table->unsignedBigInteger('created_by')->nullable()->after('plat_reserva_idplat_reserva');

            // Agregar índice para mejorar consultas
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
