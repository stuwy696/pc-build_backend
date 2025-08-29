<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre_rol' => 'Cliente',
                'descripcion' => 'Usuario cliente que puede crear armados y ver cotizaciones'
            ],
            [
                'nombre_rol' => 'Empleado',
                'descripcion' => 'Empleado que puede gestionar ventas y devoluciones'
            ],
            [
                'nombre_rol' => 'Administrador',
                'descripcion' => 'Administrador con acceso completo al sistema'
            ]
        ];

        foreach ($roles as $role) {
            Rol::create($role);
        }
    }
} 