<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends ApiController
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Get all users with pagination and search
     */
    public function index()
    {
        $user = $this->checkPermission('users.view');
        if (!is_object($user)) {
            return $user; // Return error response
        }

        $pagination = $this->getPaginationParams();
        $search = $this->request->getGet('search');
        $roleId = $this->request->getGet('role_id');
        $status = $this->request->getGet('status');

        $query = $this->userModel;

        // Apply search filter
        if (!empty($search)) {
            $query = $query->groupStart()
                          ->like('username', $search)
                          ->orLike('email', $search)
                          ->orLike('first_name', $search)
                          ->orLike('last_name', $search)
                          ->groupEnd();
        }

        // Apply role filter
        if (!empty($roleId)) {
            $query = $query->where('role_id', $roleId);
        }

        // Apply status filter
        if ($status !== null && $status !== '') {
            $query = $query->where('is_active', $status);
        }

        // Get total count for pagination
        $total = $query->countAllResults(false);

        // Get users with role information
        $users = $query->select('users.*, roles.name as role_name')
                      ->join('roles', 'roles.id = users.role_id', 'left')
                      ->orderBy('users.created_at', 'DESC')
                      ->findAll($pagination['limit'], $pagination['offset']);

        return $this->paginatedResponse($users, $total, $pagination['page'], $pagination['limit']);
    }

    /**
     * Get user by ID
     */
    public function show($id = null)
    {
        $tokenUser = $this->checkPermission('users.view');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        $user = $this->userModel->getUserWithRole($id);

        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        // Get permissions
        $permissions = [];
        if (!empty($user['permissions'])) {
            $permissions = json_decode($user['permissions'], true) ?: [];
        }
        $user['permissions_array'] = $permissions;

        return $this->successResponse($user);
    }

    /**
     * Create new user
     */
    public function create()
    {
        $tokenUser = $this->checkPermission('users.create');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        // Validate role exists
        if (isset($data['role_id'])) {
            $role = $this->roleModel->find($data['role_id']);
            if (!$role) {
                return $this->errorResponse('Invalid role selected', null, 422);
            }
        }

        if ($this->userModel->insert($data)) {
            $userId = $this->userModel->getInsertID();
            $user = $this->userModel->getUserWithRole($userId);
            
            return $this->successResponse($user, 'User created successfully', 201);
        }

        return $this->handleValidationErrors($this->userModel);
    }

    /**
     * Update user
     */
    public function update($id = null)
    {
        $tokenUser = $this->checkPermission('users.update');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        // Remove password from update data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        // Validate role exists
        if (isset($data['role_id'])) {
            $role = $this->roleModel->find($data['role_id']);
            if (!$role) {
                return $this->errorResponse('Invalid role selected', null, 422);
            }
        }

        if ($this->userModel->update($id, $data)) {
            $updatedUser = $this->userModel->getUserWithRole($id);
            return $this->successResponse($updatedUser, 'User updated successfully');
        }

        return $this->handleValidationErrors($this->userModel);
    }

    /**
     * Delete user
     */
    public function delete($id = null)
    {
        $tokenUser = $this->checkPermission('users.delete');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        // Prevent deleting own account
        if ($id == $tokenUser->user_id) {
            return $this->errorResponse('Cannot delete your own account', null, 422);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        if ($this->userModel->delete($id)) {
            return $this->successResponse(null, 'User deleted successfully');
        }

        return $this->errorResponse('Failed to delete user');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id = null)
    {
        $tokenUser = $this->checkPermission('users.update');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        // Prevent deactivating own account
        if ($id == $tokenUser->user_id) {
            return $this->errorResponse('Cannot deactivate your own account', null, 422);
        }

        if ($this->userModel->toggleStatus($id)) {
            $user = $this->userModel->find($id);
            $status = $user['is_active'] ? 'activated' : 'deactivated';
            return $this->successResponse($user, "User {$status} successfully");
        }

        return $this->errorResponse('User not found', null, 404);
    }

    /**
     * Reset user password
     */
    public function resetPassword($id = null)
    {
        $tokenUser = $this->checkPermission('users.update');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        
        $rules = [
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse($this->validator->getErrors());
        }

        $newPassword = $data['new_password'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($this->userModel->update($id, ['password' => $hashedPassword])) {
            return $this->successResponse(null, 'Password reset successfully');
        }

        return $this->errorResponse('Failed to reset password');
    }

    /**
     * Get user statistics
     */
    public function stats()
    {
        $tokenUser = $this->checkPermission('users.view');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        $totalUsers = $this->userModel->countAll();
        $activeUsers = $this->userModel->where('is_active', 1)->countAllResults(false);
        $inactiveUsers = $totalUsers - $activeUsers;

        // Users by role
        $usersByRole = $this->userModel->select('roles.name as role_name, COUNT(users.id) as count')
                                      ->join('roles', 'roles.id = users.role_id', 'left')
                                      ->groupBy('users.role_id')
                                      ->findAll();

        // Recent logins (last 30 days)
        $recentLogins = $this->userModel->where('last_login_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                                       ->countAllResults();

        $stats = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'recent_logins' => $recentLogins,
            'users_by_role' => $usersByRole
        ];

        return $this->successResponse($stats);
    }

    /**
     * Get users by role
     */
    public function getByRole($roleId = null)
    {
        $tokenUser = $this->checkPermission('users.view');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($roleId)) {
            return $this->errorResponse('Role ID is required', null, 400);
        }

        $users = $this->userModel->getUsersByRole($roleId);

        return $this->successResponse($users);
    }

    /**
     * Change user role
     */
    public function changeRole($id = null)
    {
        $tokenUser = $this->checkPermission('users.update');
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        if (empty($id)) {
            return $this->errorResponse('User ID is required', null, 400);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        
        if (empty($data['role_id'])) {
            return $this->errorResponse('Role ID is required', null, 422);
        }

        // Validate role exists
        $role = $this->roleModel->find($data['role_id']);
        if (!$role) {
            return $this->errorResponse('Invalid role selected', null, 422);
        }

        if ($this->userModel->update($id, ['role_id' => $data['role_id']])) {
            $updatedUser = $this->userModel->getUserWithRole($id);
            return $this->successResponse($updatedUser, 'User role updated successfully');
        }

        return $this->errorResponse('Failed to update user role');
    }

    /**
     * Get active users only
     */
    public function getActiveUsers()
    {
        $tokenUser = $this->validateToken();
        if (!is_object($tokenUser)) {
            return $tokenUser; // Return error response
        }

        $users = $this->userModel->getActiveUsers();

        return $this->successResponse($users);
    }
}
