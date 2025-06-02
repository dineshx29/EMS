<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@company.com',
                'phone' => '+1234567890',
                'department' => 'Information Technology',
                'position' => 'Software Engineer',
                'salary' => 75000.00,
                'hire_date' => '2023-01-15',
                'status' => 'active',
                'address' => '123 Main St, City, State 12345',
                'date_of_birth' => '1990-05-20',
                'emergency_contact' => 'Jane Doe',
                'emergency_phone' => '+1234567891',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Sarah',
                'last_name' => 'Smith',
                'email' => 'sarah.smith@company.com',
                'phone' => '+1234567892',
                'department' => 'Human Resources',
                'position' => 'HR Manager',
                'salary' => 85000.00,
                'hire_date' => '2022-08-10',
                'status' => 'active',
                'address' => '456 Oak Ave, City, State 12345',
                'date_of_birth' => '1985-12-15',
                'emergency_contact' => 'Mike Smith',
                'emergency_phone' => '+1234567893',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@company.com',
                'phone' => '+1234567894',
                'department' => 'Finance',
                'position' => 'Financial Analyst',
                'salary' => 65000.00,
                'hire_date' => '2023-03-22',
                'status' => 'active',
                'address' => '789 Pine St, City, State 12345',
                'date_of_birth' => '1992-09-30',
                'emergency_contact' => 'Lisa Johnson',
                'emergency_phone' => '+1234567895',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => 'EMP004',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@company.com',
                'phone' => '+1234567896',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'salary' => 55000.00,
                'hire_date' => '2023-06-05',
                'status' => 'active',
                'address' => '321 Elm St, City, State 12345',
                'date_of_birth' => '1993-03-18',
                'emergency_contact' => 'Tom Davis',
                'emergency_phone' => '+1234567897',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => 'EMP005',
                'first_name' => 'Robert',
                'last_name' => 'Wilson',
                'email' => 'robert.wilson@company.com',
                'phone' => '+1234567898',
                'department' => 'Information Technology',
                'position' => 'Senior Developer',
                'salary' => 95000.00,
                'hire_date' => '2021-11-12',
                'status' => 'active',
                'address' => '654 Maple Ave, City, State 12345',
                'date_of_birth' => '1988-07-25',
                'emergency_contact' => 'Amanda Wilson',
                'emergency_phone' => '+1234567899',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('employees')->insertBatch($data);
    }
}
