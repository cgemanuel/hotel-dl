<?php
// database/migrations/2025_XX_XX_add_vehicle_info_to_reservas.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('tipo_vehiculo')->nullable()->after('estacionamiento_no_espacio');
            $table->text('descripcion_vehiculo')->nullable()->after('tipo_vehiculo');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['tipo_vehiculo', 'descripcion_vehiculo']);
        });
    }
};
