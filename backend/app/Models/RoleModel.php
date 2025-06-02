<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description',
        'permissions'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[roles.name,id,{id}]',
        'description' => 'permit_empty|max_length[255]',
        'permissions' => 'permit_empty'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Role name is required',
            'is_unique' => 'Role name must be unique',
            'max_length' => 'Role name cannot exceed 100 characters'
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
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    protected function beforeUpdate(array $data)
    {
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    private function getUpdatedDataWithHashedPassword(array $data)
    {
        if (isset($data['data']['permissions']) && is_array($data['data']['permissions'])) {
            $data['data']['permissions'] = json_encode($data['data']['permissions']);
        }
        return $data;
    }

    /**
     * Get role with users count
     */
    public function getRoleWithUsersCount($id = null)
    {
        $this->select('roles.*, COUNT(users.id) as users_count')
             ->join('users', 'users.role_id = roles.id', 'left');
        
        if ($id !== null) {
            $this->where('roles.id', $id);
            return $this->first();
        }
        
        return $this->groupBy('roles.id')->findAll();
    }

    /**
     * Get permissions as array
     */
    public function getPermissionsArray($roleId)
    {
        $role = $this->find($roleId);
        if ($role && !empty($role['permissions'])) {
            return json_decode($role['permissions'], true) ?: [];
        }
        return [];
    }

    /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $permission)
    {
        $permissions = $this->getPermissionsArray($roleId);
        return in_array($permission, $permissions);
    }
}
