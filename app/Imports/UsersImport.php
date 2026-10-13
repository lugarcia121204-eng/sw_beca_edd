<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class UsersImport implements ToModel, WithHeadingRow
{
    protected $roleId;

    public function __construct($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Los encabezados de la plantilla son: "Nombre Completo", "Correo Electrónico", "Contraseña"
        // WithHeadingRow los convierte en: "nombre_completo", "correo_electronico", "contrasena"
        
        // Skip empty rows
        if (empty($row['nombre_completo']) || empty($row['correo_electronico'])) {
            return null;
        }

        // Simple validation or skip existing emails
        if (User::where('email', $row['correo_electronico'])->exists()) {
            return null;
        }

        $user = User::create([
            'name'     => $row['nombre_completo'],
            'email'    => $row['correo_electronico'],
            'password' => Hash::make($row['contrasena'] ?? 'password'), // Default password if missing
        ]);

        // Assign selected Role
        $role = Role::find($this->roleId);
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }
}
