<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'Administrador Supremo con acceso total al sistema',
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrador con permisos de gestión',
            ],
            [
                'name' => 'Evaluador',
                'description' => 'Evaluador de solicitudes',
            ],
            [
                'name' => 'Docente',
                'description' => 'Docente del sistema',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}