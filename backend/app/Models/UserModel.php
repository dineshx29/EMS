<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username',
        'email',
        'password',
        'role_id',
        'is_active',
        'last_login'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role_id' => 'required|is_natural_no_zero'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters',
            'max_length' => 'Username cannot exceed 50 characters',
            'is_unique' => 'Username already exists'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique' => 'Email already exists'
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 8 characters'
        ],
        'role_id' => [
            'required' => 'Role is required',
            'is_natural_no_zero' => 'Please select a valid role'
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
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function beforeInsert(array $data)
    {
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    protected function beforeUpdate(array $data)
    {
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    private function getUpdatedDataWithHashedPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    protected function afterFind(array $data)
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['password'])) {
                        unset($row['password']);
                    }
                }
            } else {
                if (isset($data['data']['password'])) {
                    unset($data['data']['password']);
                }
            }
        }
        return $data;
    }

    /**
     * Get user with role information
     */
    public function getUserWithRole($id = null)
    {
        $this->select('users.*, roles.name as role_name, roles.permissions')
             ->join('roles', 'roles.id = users.role_id', 'left');
        
        if ($id !== null) {
            $this->where('users.id', $id);
            return $this->first();
        }
        
        return $this->findAll();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($roleId)
    {
        return $this->where('role_id', $roleId)->findAll();
    }

    /**
     * Authenticate user
     */
    public function authenticate($login, $password)
    {
        $user = $this->where('username', $login)
                     ->orWhere('email', $login)
                     ->first();
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }

    /**
     * Get active users only
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = $this->find($id);
        if ($user) {
            return $this->update($id, ['is_active' => !$user['is_active']]);
        }
        return false;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission)
    {
        $user = $this->getUserWithRole($userId);
        if ($user && !empty($user['permissions'])) {
            $permissions = json_decode($user['permissions'], true) ?: [];
            return in_array($permission, $permissions);
        }
        return false;
    }
}
