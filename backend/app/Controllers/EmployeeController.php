<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

class EmployeeController extends ApiController
{
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
        $this->employeeModel = new EmployeeModel();
    }

    /**
     * Get all employees with pagination and search
     */
    public function index()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $pagination = $this->getPaginationParams();
        $search = $this->request->getGet('search');
        $department = $this->request->getGet('department');
        $status = $this->request->getGet('status');

        $query = $this->employeeModel;

        // Apply search filter
        if (!empty($search)) {
            $query = $query->groupStart()
                          ->like('first_name', $search)
                          ->orLike('last_name', $search)
                          ->orLike('employee_id', $search)
                          ->orLike('email', $search)
                          ->orLike('department', $search)
                          ->orLike('position', $search)
                          ->groupEnd();
        }

        // Apply department filter
        if (!empty($department)) {
            $query = $query->where('department', $department);
        }

        // Apply status filter
        if ($status !== null && $status !== '') {
            $query = $query->where('is_active', $status);
        }

        // Get total count for pagination
        $total = $query->countAllResults(false);

        // Get employees with manager information
        $employees = $query->select('employees.*, manager.first_name as manager_first_name, manager.last_name as manager_last_name')
                          ->join('employees as manager', 'manager.id = employees.manager_id', 'left')
                          ->orderBy('employees.created_at', 'DESC')
                          ->findAll($pagination['limit'], $pagination['offset']);

        return $this->paginatedResponse($employees, $total, $pagination['page'], $pagination['limit']);
    }

    /**
     * Get employee by ID
     */
    public function show($id = null)
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Employee ID is required', null, 400);
        }

        $employee = $this->employeeModel->getEmployeesWithManager($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found', null, 404);
        }

        return $this->successResponse($employee);
    }

    /**
     * Create new employee
     */
    public function create()
    {
        $user = $this->checkPermission('employees.create');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        if ($this->employeeModel->insert($data)) {
            $employeeId = $this->employeeModel->getInsertID();
            $employee = $this->employeeModel->getEmployeesWithManager($employeeId);
            
            return $this->successResponse($employee, 'Employee created successfully', 201);
        }

        return $this->handleValidationErrors($this->employeeModel);
    }

    /**
     * Update employee
     */
    public function update($id = null)
    {
        $user = $this->checkPermission('employees.update');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Employee ID is required', null, 400);
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return $this->errorResponse('Employee not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        if ($this->employeeModel->update($id, $data)) {
            $updatedEmployee = $this->employeeModel->getEmployeesWithManager($id);
            return $this->successResponse($updatedEmployee, 'Employee updated successfully');
        }

        return $this->handleValidationErrors($this->employeeModel);
    }

    /**
     * Delete employee
     */
    public function delete($id = null)
    {
        $user = $this->checkPermission('employees.delete');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Employee ID is required', null, 400);
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return $this->errorResponse('Employee not found', null, 404);
        }

        if ($this->employeeModel->delete($id)) {
            return $this->successResponse(null, 'Employee deleted successfully');
        }

        return $this->errorResponse('Failed to delete employee');
    }

    /**
     * Toggle employee status
     */
    public function toggleStatus($id = null)
    {
        $user = $this->checkPermission('employees.update');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Employee ID is required', null, 400);
        }

        if ($this->employeeModel->toggleStatus($id)) {
            $employee = $this->employeeModel->find($id);
            $status = $employee['is_active'] ? 'activated' : 'deactivated';
            return $this->successResponse($employee, "Employee {$status} successfully");
        }

        return $this->errorResponse('Employee not found', null, 404);
    }

    /**
     * Get departments list
     */
    public function departments()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $departments = $this->employeeModel->select('department')
                                          ->distinct()
                                          ->where('department IS NOT NULL')
                                          ->where('department !=', '')
                                          ->orderBy('department')
                                          ->findAll();

        $departmentList = array_column($departments, 'department');

        return $this->successResponse($departmentList);
    }

    /**
     * Get department statistics
     */
    public function departmentStats()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $stats = $this->employeeModel->getDepartmentStats();

        return $this->successResponse($stats);
    }

    /**
     * Get employee statistics
     */
    public function stats()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $stats = $this->employeeModel->getEmployeeStats();

        // Additional statistics
        $totalSalary = $this->employeeModel->selectSum('salary')
                                          ->where('is_active', 1)
                                          ->where('salary IS NOT NULL')
                                          ->first();

        $avgSalary = $this->employeeModel->selectAvg('salary')
                                        ->where('is_active', 1)
                                        ->where('salary IS NOT NULL')
                                        ->first();

        $stats['total_salary'] = $totalSalary['salary'] ?? 0;
        $stats['average_salary'] = $avgSalary['salary'] ?? 0;

        return $this->successResponse($stats);
    }

    /**
     * Get managers list
     */
    public function managers()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $managers = $this->employeeModel->getManagersList();

        return $this->successResponse($managers);
    }

    /**
     * Get employees by manager
     */
    public function getByManager($managerId = null)
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        if (empty($managerId)) {
            return $this->errorResponse('Manager ID is required', null, 400);
        }

        $employees = $this->employeeModel->getEmployeesByManager($managerId);

        return $this->successResponse($employees);
    }

    /**
     * Get employees by department
     */
    public function getByDepartment($department = null)
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        if (empty($department)) {
            return $this->errorResponse('Department is required', null, 400);
        }

        $employees = $this->employeeModel->getEmployeesByDepartment($department);

        return $this->successResponse($employees);
    }

    /**
     * Export employees data
     */
    public function export()
    {
        $user = $this->checkPermission('employees.export');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $format = $this->request->getGet('format') ?: 'csv';
        $employees = $this->employeeModel->getEmployeesWithManager();

        switch ($format) {
            case 'csv':
                return $this->exportCSV($employees);
            case 'json':
                return $this->successResponse($employees);
            default:
                return $this->errorResponse('Unsupported format', null, 400);
        }
    }

    /**
     * Export employees as CSV
     */
    private function exportCSV($employees)
    {
        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, [
            'Employee ID', 'First Name', 'Last Name', 'Email', 'Phone',
            'Date of Birth', 'Hire Date', 'Department', 'Position', 'Salary',
            'Manager', 'Address', 'Emergency Contact', 'Emergency Phone',
            'Status', 'Created At'
        ]);
        
        // CSV Data
        foreach ($employees as $employee) {
            $manager = '';
            if ($employee['manager_first_name'] && $employee['manager_last_name']) {
                $manager = $employee['manager_first_name'] . ' ' . $employee['manager_last_name'];
            }
            
            fputcsv($output, [
                $employee['employee_id'],
                $employee['first_name'],
                $employee['last_name'],
                $employee['email'],
                $employee['phone'],
                $employee['date_of_birth'],
                $employee['hire_date'],
                $employee['department'],
                $employee['position'],
                $employee['salary'],
                $manager,
                $employee['address'],
                $employee['emergency_contact'],
                $employee['emergency_phone'],
                $employee['is_active'] ? 'Active' : 'Inactive',
                $employee['created_at']
            ]);
        }
        
        fclose($output);
        exit();
    }
}
