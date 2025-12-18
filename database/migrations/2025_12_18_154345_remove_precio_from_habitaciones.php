<?php
// database/migrations/2025_XX_XX_remove_precio_from_habitaciones.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }

    public function down(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->decimal('precio', 10, 2)->default(0);
        });
    }
};
