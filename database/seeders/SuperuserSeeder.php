<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperuserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superuser@hoteldonluis.com'],
            [
                'name' => 'Superusuario',
                'telefono' => '0000000000',
                'rol' => 'superusuario',
                'password' => Hash::make('SuperHDL2025!'),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info('✅ Superusuario creado exitosamente');
            $this->command->warn('⚠️  Email: superuser@hoteldonluis.com');
            $this->command->warn('⚠️  Contraseña: SuperHDL2025!');
            $this->command->error('🔒 RECUERDA CAMBIAR ESTA CONTRASEÑA INMEDIATAMENTE');
        } else {
            $this->command->info('ℹ️  El superusuario ya existe');
        }
    }
}
