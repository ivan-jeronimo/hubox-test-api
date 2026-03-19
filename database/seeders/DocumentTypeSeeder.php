<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'Certificado de CURP',
                'code' => 'CURP_CERTIFICATE',
                'description' => 'Documento oficial que acredita la Clave Única de Registro de Población.',
                'is_active' => true,
            ],
            [
                'name' => 'Credencial de Elector (INE) - Frente',
                'code' => 'INE_FRONT',
                'description' => 'Parte frontal de la Credencial para Votar (INE/IFE).',
                'is_active' => true,
            ],
            [
                'name' => 'Credencial de Elector (INE) - Reverso',
                'code' => 'INE_BACK',
                'description' => 'Parte trasera de la Credencial para Votar (INE/IFE).',
                'is_active' => true,
            ],
            // Puedes añadir más tipos aquí, por ejemplo:
            // [
            //     'name' => 'Pasaporte',
            //     'code' => 'PASSPORT',
            //     'description' => 'Documento oficial de viaje.',
            //     'is_active' => true,
            // ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
