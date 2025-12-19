<?php
// database/migrations/2025_XX_XX_add_total_reserva_to_reservas.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Añadir campo para el total de la reserva
            $table->decimal('total_reserva', 10, 2)->nullable()->after('monto_transferencia');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('total_reserva');
        });
    }
};
