<?php

namespace App\Models;

use CodeIgniter\Model;

class UserMobileModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'email',
        'avatar',
        'first_name',
        'last_name',
        'about_me',
    ];

    /**
     * Get user profile by ID
     */
    public function getUserProfile($userId)
    {
        return $this->select([
            'id',
            'email',
            'avatar',
            'first_name',
            'last_name',
            'about_me'
        ])
            ->where('id', $userId)
            ->first();
    }

    /**
     * Update user profile
     */
    public function updateUserProfile($userId, array $data)
    {
        return $this->where('id', $userId)
            ->set($data)
            ->update();
    }
}