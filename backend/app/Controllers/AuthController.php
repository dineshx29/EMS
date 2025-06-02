<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends ApiController
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
     * User login
     */
    public function login()
    {
        $rules = [
            'login' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse($this->validator->getErrors());
        }

        $login = $this->request->getPost('login');
        $password = $this->request->getPost('password');

        // Authenticate user
        $user = $this->userModel->authenticate($login, $password);

        if (!$user) {
            return $this->errorResponse('Invalid credentials', null, 401);
        }

        if (!$user['is_active']) {
            return $this->errorResponse('Account is deactivated', null, 403);
        }

        // Get user with role
        $userWithRole = $this->userModel->getUserWithRole($user['id']);
        
        // Get permissions
        $permissions = [];
        if (!empty($userWithRole['permissions'])) {
            $permissions = json_decode($userWithRole['permissions'], true) ?: [];
        }

        // Generate JWT token
        $key = getenv('JWT_SECRET') ?: 'your-secret-key';
        $payload = [
            'iss' => 'ems-api',
            'aud' => 'ems-frontend',
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60), // 24 hours
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $userWithRole['role_name'] ?? '',
            'permissions' => $permissions
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role_id' => $user['role_id'],
                'role_name' => $userWithRole['role_name'] ?? '',
                'permissions' => $permissions,
                'is_active' => $user['is_active'],
                'last_login' => $user['last_login'] ?? null
            ]
        ], 'Login successful');
    }

    /**
     * User logout
     */
    public function logout()
    {
        // In a more advanced system, you would blacklist the token
        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * Get current user profile
     */
    public function profile()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $user = $this->userModel->getUserWithRole($tokenData->user_id);
        
        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        $permissions = [];
        if (!empty($user['permissions'])) {
            $permissions = json_decode($user['permissions'], true) ?: [];
        }

        return $this->successResponse([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'] ?? '',
            'permissions' => $permissions,
            'is_active' => $user['is_active'],
            'last_login' => $user['last_login'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse($this->validator->getErrors());
        }

        $data = [
            'email' => $this->request->getPost('email')
        ];

        // Check if email is unique (excluding current user)
        $existingUser = $this->userModel->where('email', $data['email'])
                                        ->where('id !=', $tokenData->user_id)
                                        ->first();
        
        if ($existingUser) {
            return $this->errorResponse('Email already exists', null, 422);
        }

        if ($this->userModel->update($tokenData->user_id, $data)) {
            return $this->successResponse(null, 'Profile updated successfully');
        }

        return $this->errorResponse('Failed to update profile');
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse($this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Get user with password
        $user = $this->userModel->select('id, password')
                               ->find($tokenData->user_id);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return $this->errorResponse('Current password is incorrect', null, 422);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->userModel->update($tokenData->user_id, ['password' => $hashedPassword])) {
            return $this->successResponse(null, 'Password changed successfully');
        }

        return $this->errorResponse('Failed to change password');
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        $tokenData = $this->validateToken();
        if (!is_object($tokenData)) {
            return $tokenData; // Return error response
        }

        // Get fresh user data
        $user = $this->userModel->getUserWithRole($tokenData->user_id);
        
        if (!$user || !$user['is_active']) {
            return $this->errorResponse('User account is not active', null, 403);
        }

        // Get permissions
        $permissions = [];
        if (!empty($user['permissions'])) {
            $permissions = json_decode($user['permissions'], true) ?: [];
        }

        // Generate new JWT token
        $key = getenv('JWT_SECRET') ?: 'your-secret-key';
        $payload = [
            'iss' => 'ems-api',
            'aud' => 'ems-frontend',
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60), // 24 hours
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'] ?? '',
            'permissions' => $permissions
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name'] ?? '',
                'permissions' => $permissions,
                'is_active' => $user['is_active'],
                'last_login_at' => $user['last_login_at']
            ]
        ], 'Token refreshed successfully');
    }
}
