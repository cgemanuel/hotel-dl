<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Agregar columnas específicas para débito y crédito
            $table->decimal('monto_tarjeta_debito', 10, 2)->nullable()->default(0)->after('monto_tarjeta');
            $table->decimal('monto_tarjeta_credito', 10, 2)->nullable()->default(0)->after('monto_tarjeta_debito');
        });

        // Migrar datos existentes: monto_tarjeta existente → monto_tarjeta_debito
        DB::statement('UPDATE reservas SET monto_tarjeta_debito = monto_tarjeta WHERE monto_tarjeta > 0 AND metodo_pago IN ("tarjeta", "tarjeta_debito")');
        DB::statement('UPDATE reservas SET monto_tarjeta_credito = monto_tarjeta WHERE monto_tarjeta > 0 AND metodo_pago = "tarjeta_credito"');
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['monto_tarjeta_debito', 'monto_tarjeta_credito']);
        });
    }
};
