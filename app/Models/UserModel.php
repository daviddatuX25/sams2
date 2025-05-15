<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
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
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
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
    protected $validationMessages = [];
    protected $skipValidation = false;
}