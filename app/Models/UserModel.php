<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'password_hash',
        'is_password_temporary', 'role', 'status', 'gender', 'bio', 'profile_picture', 'deleted_at' // Added is_password_temporary
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'user_key' => 'required|is_unique[users.user_key,user_id,{user_id}]|max_length[255]',
        'first_name' => 'required|max_length[255]',
        'last_name' => 'required|max_length[255]',
        'middle_name' => 'permit_empty|max_length[255]',
        'birthday' => 'permit_empty|valid_date',
        'password_hash' => 'required|min_length[8]', // Kept min_length for consistency, but will bypass for reset
        'is_password_temporary' => 'permit_empty|in_list[0,1]', // Added validation for new field
        'role' => 'required|in_list[student,teacher,admin]',
        'status' => 'required|in_list[active,pending,archived]',
        'gender' => 'required|max_length[50]',
        'bio' => 'permit_empty',
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
            'in_list' => 'The status must be one of: student, teacher, admin.'
        ],
        'gender' => [
            'required' => 'The gender is required.',
            'max_length' => 'The gender cannot exceed 50 characters.'
        ],
        'profile_picture' => [
            'max_length' => 'The profile picture path cannot exceed 255 characters.'
        ]
    ];

    public function getUser($userId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($userId);
        }
        return $this->find($userId);
    }

    public function userExists($userKey, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('user_key', $userKey)->get()->getRow() !== null;
    }

    public function authenticateUserByPassword($userKey, $password)
    {
        $user = $this->where('user_key', $userKey)->first();
        if ($user) {
            // Check if the password is temporary (plain-text)
            if ($user['is_password_temporary'] == 1) {
                // Compare plain-text password
                if ($password === $user['password_hash']) {
                    return [
                        'user_id' => $user['user_id'],
                        'first_name' => $user['first_name'],
                        'role' => $user['role'],
                        'profile_picture' => $user['profile_picture'],
                        'status' => $user['status'],
                        'is_password_temporary' => $user['is_password_temporary'] // Include for UI to prompt password change
                    ];
                }
            } else {
                // Compare hashed password
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
        // Always hash the password for new users, assume not temporary
        if (isset($userData['password_hash'])) {
            $userData['password_hash'] = password_hash($userData['password_hash'], PASSWORD_DEFAULT);
            $userData['is_password_temporary'] = 0; // Set to non-temporary
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
        // If password_hash is provided, hash it and set is_password_temporary to 0
        if (isset($userData['password_hash'])) {
            $userData['password_hash'] = password_hash($userData['password_hash'], PASSWORD_DEFAULT);
            $userData['is_password_temporary'] = 0;
        }
        return $this->update($userId, $userData);
    }

    public function resetPassword($userId, $newPassword)
    {
        // Store plain-text password and mark as temporary
        $userData = [
            'password_hash' => $newPassword,
            'is_password_temporary' => 1
        ];
        // Bypass min_length validation for password reset
        $this->validationRules['password_hash'] = 'required';
        if (!$this->validate($userData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        return $this->update($userId, $userData);
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $user = $this->find($userId);
        if ($user) {
            // Check old password (plain-text or hashed)
            $isValidOldPassword = $user['is_password_temporary'] == 1
                ? $oldPassword === $user['password_hash']
                : password_verify($oldPassword, $user['password_hash']);
            if ($isValidOldPassword) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                return $this->update($userId, [
                    'password_hash' => $newHash,
                    'is_password_temporary' => 0 // Reset temporary flag
                ]);
            }
        }
        return false;
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