<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@ems.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role_id' => 1, // Admin role
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'hr_manager',
                'email' => 'hr@ems.com',
                'password' => password_hash('hr123', PASSWORD_DEFAULT),
                'role_id' => 2, // HR Manager role
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'employee',
                'email' => 'employee@ems.com',
                'password' => password_hash('emp123', PASSWORD_DEFAULT),
                'role_id' => 3, // Employee role
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
