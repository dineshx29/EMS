<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;

class RoleController extends ApiController
{
    protected $roleModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->roleModel = new RoleModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get all roles with pagination and search
     */
    public function index()
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $pagination = $this->getPaginationParams();
        $search = $this->request->getGet('search');

        $query = $this->roleModel;

        // Apply search filter
        if (!empty($search)) {
            $query = $query->groupStart()
                          ->like('name', $search)
                          ->orLike('description', $search)
                          ->groupEnd();
        }

        // Get total count for pagination
        $total = $query->countAllResults(false);

        // Get roles with users count
        $roles = $query->select('roles.*, COUNT(users.id) as users_count')
                      ->join('users', 'users.role_id = roles.id', 'left')
                      ->groupBy('roles.id')
                      ->orderBy('roles.created_at', 'DESC')
                      ->findAll($pagination['limit'], $pagination['offset']);

        // Parse permissions for each role
        foreach ($roles as &$role) {
            if (!empty($role['permissions'])) {
                $role['permissions_array'] = json_decode($role['permissions'], true) ?: [];
            } else {
                $role['permissions_array'] = [];
            }
        }

        return $this->paginatedResponse($roles, $total, $pagination['page'], $pagination['limit']);
    }

    /**
     * Get role by ID
     */
    public function show($id = null)
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $role = $this->roleModel->getRoleWithUsersCount($id);

        if (!$role) {
            return $this->errorResponse('Role not found', null, 404);
        }

        // Parse permissions
        if (!empty($role['permissions'])) {
            $role['permissions_array'] = json_decode($role['permissions'], true) ?: [];
        } else {
            $role['permissions_array'] = [];
        }

        return $this->successResponse($role);
    }

    /**
     * Create new role
     */
    public function create()
    {
        $user = $this->checkPermission('roles.create');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        // Convert permissions array to JSON string
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        if ($this->roleModel->insert($data)) {
            $roleId = $this->roleModel->getInsertID();
            $role = $this->roleModel->getRoleWithUsersCount($roleId);
            
            // Parse permissions for response
            if (!empty($role['permissions'])) {
                $role['permissions_array'] = json_decode($role['permissions'], true) ?: [];
            } else {
                $role['permissions_array'] = [];
            }
            
            return $this->successResponse($role, 'Role created successfully', 201);
        }

        return $this->handleValidationErrors($this->roleModel);
    }

    /**
     * Update role
     */
    public function update($id = null)
    {
        $user = $this->checkPermission('roles.update');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        // Convert permissions array to JSON string
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        if ($this->roleModel->update($id, $data)) {
            $updatedRole = $this->roleModel->getRoleWithUsersCount($id);
            
            // Parse permissions for response
            if (!empty($updatedRole['permissions'])) {
                $updatedRole['permissions_array'] = json_decode($updatedRole['permissions'], true) ?: [];
            } else {
                $updatedRole['permissions_array'] = [];
            }
            
            return $this->successResponse($updatedRole, 'Role updated successfully');
        }

        return $this->handleValidationErrors($this->roleModel);
    }

    /**
     * Delete role
     */
    public function delete($id = null)
    {
        $user = $this->checkPermission('roles.delete');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', null, 404);
        }

        // Check if role is assigned to any users
        $usersCount = $this->userModel->where('role_id', $id)->countAllResults();
        if ($usersCount > 0) {
            return $this->errorResponse('Cannot delete role that is assigned to users', null, 422);
        }

        if ($this->roleModel->delete($id)) {
            return $this->successResponse(null, 'Role deleted successfully');
        }

        return $this->errorResponse('Failed to delete role');
    }

    /**
     * Get all roles (simplified list for dropdowns)
     */
    public function getAllRoles()
    {
        $user = $this->validateToken();
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $roles = $this->roleModel->select('id, name, description')
                                ->orderBy('name')
                                ->findAll();

        return $this->successResponse($roles);
    }

    /**
     * Get available permissions
     */
    public function getPermissions()
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $permissions = [
            'users' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.update' => 'Update Users',
                'users.delete' => 'Delete Users'
            ],
            'employees' => [
                'employees.view' => 'View Employees',
                'employees.create' => 'Create Employees',
                'employees.update' => 'Update Employees',
                'employees.delete' => 'Delete Employees',
                'employees.export' => 'Export Employees'
            ],
            'roles' => [
                'roles.view' => 'View Roles',
                'roles.create' => 'Create Roles',
                'roles.update' => 'Update Roles',
                'roles.delete' => 'Delete Roles'
            ],
            'reports' => [
                'reports.view' => 'View Reports',
                'reports.export' => 'Export Reports'
            ],
            'settings' => [
                'settings.view' => 'View Settings',
                'settings.update' => 'Update Settings'
            ]
        ];

        return $this->successResponse($permissions);
    }

    /**
     * Check role permissions
     */
    public function checkRolePermissions($id = null)
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $permissions = $this->roleModel->getPermissionsArray($id);

        return $this->successResponse($permissions);
    }

    /**
     * Get role statistics
     */
    public function stats()
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $totalRoles = $this->roleModel->countAll();
        
        // Roles with users count
        $rolesWithUsers = $this->roleModel->select('roles.name, COUNT(users.id) as users_count')
                                         ->join('users', 'users.role_id = roles.id', 'left')
                                         ->groupBy('roles.id')
                                         ->findAll();

        // Most used role
        $mostUsedRole = $this->roleModel->select('roles.name, COUNT(users.id) as users_count')
                                       ->join('users', 'users.role_id = roles.id', 'left')
                                       ->groupBy('roles.id')
                                       ->orderBy('users_count', 'DESC')
                                       ->first();

        $stats = [
            'total_roles' => $totalRoles,
            'roles_with_users' => $rolesWithUsers,
            'most_used_role' => $mostUsedRole
        ];

        return $this->successResponse($stats);
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions($id = null)
    {
        $user = $this->checkPermission('roles.update');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        
        if (!isset($data['permissions']) || !is_array($data['permissions'])) {
            return $this->errorResponse('Permissions array is required', null, 422);
        }

        $permissions = json_encode($data['permissions']);

        if ($this->roleModel->update($id, ['permissions' => $permissions])) {
            $updatedRole = $this->roleModel->find($id);
            $updatedRole['permissions_array'] = $data['permissions'];
            
            return $this->successResponse($updatedRole, 'Permissions assigned successfully');
        }

        return $this->errorResponse('Failed to assign permissions');
    }

    /**
     * Get users by role
     */
    public function getRoleUsers($id = null)
    {
        $user = $this->checkPermission('roles.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $users = $this->userModel->getUsersByRole($id);

        return $this->successResponse($users);
    }
}
