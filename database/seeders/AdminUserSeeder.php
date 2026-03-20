<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Importar el modelo User
use Illuminate\Support\Facades\Hash; // Para hashear la contraseña

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el usuario administrador
        User::firstOrCreate(
            ['email' => 'mauroivaning@gmail.com'], // Buscar por email
            [
                'first_name' => 'Mauro',
                'paternal_surname' => 'Ivaning',
                'password' => Hash::make('password'), // Contraseña por defecto, ¡CAMBIAR EN PRODUCCIÓN!
                'is_admin' => true,
                'email_verified_at' => now(), // Marcar como verificado
                // Puedes añadir otros campos si son requeridos y no tienen valor por defecto
            ]
        );

        $this->command->info('Usuario administrador creado/actualizado: mauroivaning@gmail.com');
    }
}
