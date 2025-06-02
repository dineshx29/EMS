<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'hire_date',
        'department',
        'position',
        'salary',
        'manager_id',
        'address',
        'emergency_contact',
        'emergency_phone',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'employee_id' => 'required|max_length[20]|is_unique[employees.employee_id,id,{id}]',
        'first_name' => 'required|max_length[100]',
        'last_name' => 'required|max_length[100]',
        'email' => 'required|valid_email|is_unique[employees.email,id,{id}]',
        'phone' => 'permit_empty|max_length[20]',
        'date_of_birth' => 'permit_empty|valid_date',
        'hire_date' => 'required|valid_date',
        'department' => 'required|max_length[100]',
        'position' => 'required|max_length[100]',
        'salary' => 'permit_empty|decimal',
        'manager_id' => 'permit_empty|is_natural',
        'address' => 'permit_empty',
        'emergency_contact' => 'permit_empty|max_length[100]',
        'emergency_phone' => 'permit_empty|max_length[20]'
    ];

    protected $validationMessages = [
        'employee_id' => [
            'required' => 'Employee ID is required',
            'is_unique' => 'Employee ID already exists',
            'max_length' => 'Employee ID cannot exceed 20 characters'
        ],
        'first_name' => [
            'required' => 'First name is required',
            'max_length' => 'First name cannot exceed 100 characters'
        ],
        'last_name' => [
            'required' => 'Last name is required',
            'max_length' => 'Last name cannot exceed 100 characters'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique' => 'Email already exists'
        ],
        'hire_date' => [
            'required' => 'Hire date is required',
            'valid_date' => 'Please provide a valid hire date'
        ],
        'department' => [
            'required' => 'Department is required',
            'max_length' => 'Department cannot exceed 100 characters'
        ],
        'position' => [
            'required' => 'Position is required',
            'max_length' => 'Position cannot exceed 100 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function beforeInsert(array $data)
    {
        return $this->processEmployeeData($data);
    }

    protected function beforeUpdate(array $data)
    {
        return $this->processEmployeeData($data);
    }

    private function processEmployeeData(array $data)
    {
        // Generate employee ID if not provided
        if (!isset($data['data']['employee_id']) || empty($data['data']['employee_id'])) {
            $data['data']['employee_id'] = $this->generateEmployeeId();
        }
        
        return $data;
    }

    /**
     * Generate unique employee ID
     */
    private function generateEmployeeId()
    {
        $prefix = 'EMP';
        $year = date('Y');
        
        // Get the last employee ID for current year
        $lastEmployee = $this->like('employee_id', $prefix . $year, 'after')
                            ->orderBy('employee_id', 'DESC')
                            ->first();
        
        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee['employee_id'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $year . $newNumber;
    }

    /**
     * Get employees with manager information
     */
    public function getEmployeesWithManager($id = null)
    {
        $this->select('employees.*, manager.first_name as manager_first_name, manager.last_name as manager_last_name')
             ->join('employees as manager', 'manager.id = employees.manager_id', 'left');
        
        if ($id !== null) {
            $this->where('employees.id', $id);
            return $this->first();
        }
        
        return $this->findAll();
    }

    /**
     * Get employees by department
     */
    public function getEmployeesByDepartment($department)
    {
        return $this->where('department', $department)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get employees by manager
     */
    public function getEmployeesByManager($managerId)
    {
        return $this->where('manager_id', $managerId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Search employees
     */
    public function searchEmployees($search, $limit = 10, $offset = 0)
    {
        $this->groupStart()
             ->like('first_name', $search)
             ->orLike('last_name', $search)
             ->orLike('employee_id', $search)
             ->orLike('email', $search)
             ->orLike('department', $search)
             ->orLike('position', $search)
             ->groupEnd();
        
        return $this->findAll($limit, $offset);
    }

    /**
     * Get active employees only
     */
    public function getActiveEmployees()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get department statistics
     */
    public function getDepartmentStats()
    {
        return $this->select('department, COUNT(*) as count, AVG(salary) as avg_salary')
                    ->where('is_active', 1)
                    ->groupBy('department')
                    ->findAll();
    }

    /**
     * Get employees count by status
     */
    public function getEmployeeStats()
    {
        $total = $this->countAll();
        $active = $this->where('is_active', 1)->countAllResults(false);
        $inactive = $total - $active;
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ];
    }

    /**
     * Toggle employee status
     */
    public function toggleStatus($id)
    {
        $employee = $this->find($id);
        if ($employee) {
            return $this->update($id, ['is_active' => !$employee['is_active']]);
        }
        return false;
    }

    /**
     * Get managers list (employees who can be managers)
     */
    public function getManagersList()
    {
        return $this->select('id, first_name, last_name, employee_id, position')
                    ->where('is_active', 1)
                    ->whereIn('position', ['Manager', 'Senior Manager', 'Director', 'VP', 'CEO'])
                    ->findAll();
    }
}
