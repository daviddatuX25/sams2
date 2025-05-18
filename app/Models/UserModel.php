<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends BaseModel
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'user_key',
        'password_hash',
        'is_password_temporary',
        'role',
        'status',
        'first_name',
        'last_name',
        'middle_name',
        'birthday',
        'gender',
        'bio',
        'profile_picture',
        'deleted_at'
    ];
    protected $validationRules = [
        'user_key' => 'required|is_unique[user.user_key,user_id,{user_id}]|max_length[255]',
        'password_hash' => 'required|max_length[255]',
        'is_password_temporary' => 'in_list[0,1]',
        'role' => 'required|in_list[student,teacher,admin]',
        'status' => 'required|in_list[active,pending,archived]',
        'first_name' => 'required|max_length[255]',
        'last_name' => 'required|max_length[255]',
        'middle_name' => 'permit_empty|max_length[255]',
        'birthday' => 'permit_empty|valid_date',
        'gender' => 'permit_empty|in_list[male,female,other]',
        'bio' => 'permit_empty',
        'profile_picture' => 'permit_empty|max_length[255]'
    ];
    protected $validationMessages = [
    'user_key' => [
        'required' => 'The user key is required.',
        'is_unique' => 'This user key is already in use. Please choose a different one.',
        'max_length' => 'The user key cannot exceed 255 characters.'
    ],
    'password_hash' => [
        'required' => 'The password is required.',
        'max_length' => 'The password cannot exceed 255 characters.'
    ],
    'is_password_temporary' => [
        'in_list' => 'The password temporary status must be either 0 or 1.'
    ],
    'role' => [
        'required' => 'The role is required.',
        'in_list' => 'The role must be either student, teacher, or admin.'
    ],
    'status' => [
        'required' => 'The status is required.',
        'in_list' => 'The status must be either active, pending, or archived.'
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
    'gender' => [
        'in_list' => 'The gender must be either male, female, or other.'
    ],
    'profile_picture' => [
        'max_length' => 'The profile picture path cannot exceed 255 characters.'
    ]
];
    protected $skipValidation = false;

}
