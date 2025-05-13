<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'password_hash',
        'is_password_temporary', 'role', 'status', 'gender', 'bio', 'profile_picture', 'deleted_at'
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'user_key' => 'required|is_unique[users.user_key]|max_length[255]',
        'first_name' => 'required|max_length[255]',
        'last_name' => 'required|max_length[255]',
        'middle_name' => 'permit_empty|max_length[255]',
        'birthday' => 'permit_empty|valid_date',
        'password_hash' => 'required|min_length[8]',
        'is_password_temporary' => 'permit_empty|in_list[0,1]',
        'role' => 'required|in_list[student,teacher,admin]',
        'status' => 'required|in_list[active,pending,archived]',
        'gender' => 'required|in_list[male,female,other]',
        'bio' => 'permit_empty|max_length[500]',
        'profile_picture' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [
        'user_key' => [
            'required' => 'The user key is required.',
            'is_unique' => 'The user key must be unique.',
            'max_length' => 'The user key cannot exceed 255 characters.'
        ],
        'first_name' => [
            'required' => 'The first name is required.',
            'max_length' => 'The first name cannot exceed 255 characters.'
        ],
        'last_name' => [
            'required' => 'The last name is required.',
            'max_length' => 'The last name cannot exceed 255 characters.'
        ],
        'middle_name' => [
            'max_length' => 'The middle name cannot exceed 255 characters.'
        ],
        'birthday' => [
            'valid_date' => 'The birthday must be a valid date.'
        ],
        'password_hash' => [
            'required' => 'The password is required.',
            'min_length' => 'The password must be at least 8 characters long.'
        ],
        'is_password_temporary' => [
            'in_list' => 'The temporary password flag must be 0 or 1.'
        ],
        'role' => [
            'required' => 'The role is required.',
            'in_list' => 'The role must be one of: student, teacher, admin.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: active, pending, archived.'
        ],
        'gender' => [
            'required' => 'The gender is required.',
            'in_list' => 'The gender must be one of: male, female, other.'
        ],
        'bio' => [
            'max_length' => 'The bio cannot exceed 500 characters.'
        ],
        'profile_picture' => [
            'max_length' => 'The profile picture path cannot exceed 255 characters.'
        ]
    ];

    public function isUserKeyUnique($userKey, $excludeUserId = null)
    {
        $builder = $this->select('user_key')->where('user_key', $userKey);
        if ($excludeUserId) {
            $builder->where('user_id !=', $excludeUserId);
        }
        return $builder->get()->getRow() === null;
    }

    public function updateProfile($userId, $userData)
    {
        if(!$this->isUserKeyUnique($userData['user_key'])){
            unset($userData['user_key']);
        }
        unset($userData['user_id']);
        return $this->update($userId, $userData);
    }

    public function updateProfilePicture($userId, $photoPath)
    {
        $data = ['profile_picture' => $photoPath];
        if (!$this->validate($data)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        return $this->update($userId, $data);
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $user = $this->find($userId);
        if ($user) {
            $isValidOldPassword = $user['is_password_temporary'] == 1
                ? $oldPassword === $user['password_hash']
                : password_verify($oldPassword, $user['password_hash']);
            if ($isValidOldPassword) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                return $this->update($userId, [
                    'password_hash' => $newHash,
                    'is_password_temporary' => 0
                ]);
            }
        }
        return false;
    }

    public function getUser($userId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($userId);
        }
        return $this->find($userId);
    }

     public function getUsers($limit = null, $offset = 0, $search = null)
    {
        $builder = $this->builder();

        if ($search) {
            $builder->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike('user_key', $search);
        }

        return $builder->get($limit, $offset)->getResultArray();
    }

    public function getRole($userId) 
    {
        $user = $this->find($userId);
        return $user ? $user['role'] : null;
    }

    public function getUserDetails($userId) 
    {
        return $this->select('user_id, user_key, first_name, last_name, middle_name, birthday, gender, bio, profile_picture, role, status')
                    ->find($userId);
    }

    public function isRole($userId, $role) 
    {
        $user = $this->find($userId);
        return $user && $user['role'] === $role;
    }

    public function userExists($userKey, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('user_key', $userKey)->get()->getRow() !== null;
    }

    public function getTotalUsers($search = null)
    {
        $builder = $this->builder();

        if ($search) {
            $builder->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike('user_key', $search);
        }

        return $builder->countAllResults();
    }

    public function authenticateUserByPassword($userKey, $password)
    {
        $user = $this->where('user_key', $userKey)->first();
        if ($user) {
            if ($user['is_password_temporary'] == 1) {
                if ($password === $user['password_hash']) {
                    return [
                        'user_id' => $user['user_id'],
                        'first_name' => $user['first_name'],
                        'role' => $user['role'],
                        'profile_picture' => $user['profile_picture'],
                        'status' => $user['status'],
                        'is_password_temporary' => $user['is_password_temporary']
                    ];
                }
            } else {
                if (password_verify($password, $user['password_hash'])) {
                    return [
                        'user_id' => $user['user_id'],
                        'first_name' => $user['first_name'],
                        'role' => $user['role'],
                        'profile_picture' => $user['profile_picture'],
                        'status' => $user['status'],
                        'is_password_temporary' => $user['is_password_temporary']
                    ];
                }
            }
        }
        return false;
    }

    public function createUser($userData)
    {
        unset($userData['password_confirm']);
        if (isset($userData['password'])) {
            $userData['password_hash'] = $userData['password'];
            unset($userData['password']);
        }
        if (!$this->validate($userData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        if (isset($userData['password_hash'])) {
            $userData['password_hash'] = password_hash($userData['password_hash'], PASSWORD_DEFAULT);
            $userData['is_password_temporary'] = 0;
        }
        $this->insert($userData);
        $userId = $this->getInsertID();
        return $this->select('user_id, first_name, role, profile_picture, status, is_password_temporary')->find($userId);
    }

    public function updateUser($userId, $userData)
    {
        $userData['user_id'] = $userId;
        if (!$this->validate($userData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($userData['user_id']);
        if (isset($userData['password_hash'])) {
            $userData['password_hash'] = password_hash($userData['password_hash'], PASSWORD_DEFAULT);
            $userData['is_password_temporary'] = 0;
        }
        return $this->update($userId, $userData);
    }

    public function resetPassword($userId, $newPassword)
    {
        $userData = [
            'password_hash' => $newPassword,
            'is_password_temporary' => 1
        ];
        $this->validationRules['password_hash'] = 'required';
        if (!$this->validate($userData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        return $this->update($userId, $userData);
    }

    public function hasTemporaryPassword($userId)
    {
        $user = $this->find($userId);
        return $user && $user['is_password_temporary'] == 1;
    }

    public function getUsersByRole($role, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('role', $role)->findAll();
        }
        return $this->where('role', $role)->findAll();
    }

    public function searchUsers($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('first_name', $searchTerm)
                       ->orLike('last_name', $searchTerm)
                       ->orLike('user_key', $searchTerm)
                       ->get()->getResultArray();
    }

    public function softDelete($userId)
    {
        try {
            $this->delete($userId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($userId)
    {
        try {
            $this->onlyDeleted()->delete($userId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($userId)
    {
        try {
            $this->onlyDeleted()->update($userId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}