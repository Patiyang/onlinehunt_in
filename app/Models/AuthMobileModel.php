<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthMobileModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'email', 'password', 'avatar', 'cover_image', 'slug', 'token', 'about_me', 'last_seen'];
    protected $returnType = 'array';

    protected $builder;
    protected $builderRoles;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('users');
        $this->builderRoles = $this->db->table('roles');
    }
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['slug'] = $this->generateUniqueSlug($data['username']);
        $data['status'] = 1;
        $data['email_status'] = 1;
        $data['role_id'] = 3; // Default role for new users
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['last_seen'] = date('Y-m-d H:i:s');
        $data['auth_token'] = generateAuthToken(true); // Generate a short token
        return $this->insert($data);
    }
    public function generateUniqueSlug($username)
    {
        $slug = strSlug($username);
        $originalSlug = $slug;
        $counter = 1;
        while (!empty($this->getUserBySlug($slug))) {
            $slug = strSlug($originalSlug . '-' . $counter);
            $counter++;
            if ($counter > 100) {
                $slug = strSlug($originalSlug . '-' . uniqid());
                break;
            }
        }
        return $slug;
    }

    public function getUserBySlug($slug)
    {
        return $this->builder->where('slug', cleanSlug($slug))->get()->getRow();
    }
}
