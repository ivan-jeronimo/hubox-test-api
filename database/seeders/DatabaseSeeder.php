<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llama a otros seeders aquí
        $this->call([
            DocumentTypeSeeder::class,
            AdminUserSeeder::class, // Añadido para ejecutar el seeder del administrador
        ]);

        // Puedes mantener o eliminar el seeder de usuario de prueba si lo necesitas
        // User::factory(10)->create();
        // User::factory()->create([
        //     'first_name' => 'Test',
        //     'paternal_surname' => 'User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
