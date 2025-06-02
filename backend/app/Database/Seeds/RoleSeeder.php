<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Admin',
                'description' => 'System Administrator with full access',
                'permissions' => json_encode([
                    'users_create', 'users_read', 'users_update', 'users_delete',
                    'employees_create', 'employees_read', 'employees_update', 'employees_delete',
                    'roles_create', 'roles_read', 'roles_update', 'roles_delete'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Human Resources Manager',
                'permissions' => json_encode([
                    'employees_create', 'employees_read', 'employees_update', 'employees_delete',
                    'users_read'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Employee',
                'description' => 'Regular Employee',
                'permissions' => json_encode([
                    'employees_read'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}
