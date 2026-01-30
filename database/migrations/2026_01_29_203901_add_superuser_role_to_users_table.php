<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar la columna rol para incluir 'superusuario'
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('recepcionista', 'gerente', 'superusuario') NOT NULL DEFAULT 'recepcionista'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('recepcionista', 'gerente') NOT NULL DEFAULT 'recepcionista'");
    }
};
