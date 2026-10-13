<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener Roles existentes (creados por RoleSeeder)
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $admin      = Role::where('name', 'Admin')->first();

        // ────────────────────────────────────────────────
        // Usuarios originales (sin modificar)
        // ────────────────────────────────────────────────
        $userSuperAdmin = User::updateOrCreate([
            'email' => 'superadmin@gmail.com',
        ], [
            'name'               => 'DIEGO EDUARDO JAIMEZ FLORES',
            'password'           => Hash::make('password'),
            'curp'               => 'JAFD940101HDFRRL09',
            'email_verified_at'  => now(),
        ]);
        if ($superAdmin) {
            $userSuperAdmin->syncRoles([$superAdmin->name]);
        }

        $userAdmin = User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name'               => 'ABRAHAM AVELINO PICHARDO',
            'password'           => Hash::make('password'),
            'curp'               => 'PICA890101HDFRRL09',
            'email_verified_at'  => now(),
        ]);
        if ($admin) {
            $userAdmin->syncRoles([$admin->name]);
        }

    }
}