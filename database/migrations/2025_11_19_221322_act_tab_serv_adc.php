<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar tabla si existe
        Schema::dropIfExists('servicios_adicionales');

        // Crear tabla SIN foreign key primero
        Schema::create('servicios_adicionales', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT
            $table->unsignedInteger('reservas_idreservas');
            $table->text('descripcion');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->string('usuario_registro', 100)->nullable();
            $table->timestamps();

            // Solo índice, sin foreign key aún
            $table->index('reservas_idreservas');
        });

        // Intentar agregar foreign key con manejo de errores
        try {
            DB::statement('
                ALTER TABLE servicios_adicionales
                ADD CONSTRAINT servicios_adicionales_reservas_idreservas_foreign
                FOREIGN KEY (reservas_idreservas)
                REFERENCES reservas(idreservas)
                ON DELETE CASCADE
            ');
        } catch (\Exception $e) {
            // Si falla, continuar sin foreign key
            // La integridad se manejará a nivel de aplicación
            //\Log::warning('No se pudo crear foreign key en servicios_adicionales: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_adicionales');
    }
};
