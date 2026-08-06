<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AuthMobileModel;

class AuthController extends BaseController
{
    protected $authModel;

    public function __construct()
    {
        $this->authModel = new AuthMobileModel();
        helper('auth');
    }

    public function signup()
    {
        $data = json_decode($this->request->getBody(), true);

        if (!$data['email'] || !$data['password'] || !$data['username']) {
            return $this->response->setJSON(['error' => 'Missing fields'])->setStatusCode(400);
        }

        if ($this->authModel->getUserByEmail($data['email'])) {
            return $this->response->setJSON(['error' => 'Email already exists'])->setStatusCode(409);
        }

        $userId = $this->authModel->createUser($data);
        $user   = $this->authModel->find($userId);

        $sessionKey = getAuthSessionkey($user);

        return $this->response->setJSON([
            'user'  => $user,
            'token' => $sessionKey
        ]);
    }

    public function login()
    {
        $data = json_decode($this->request->getBody(), true);

        if (!$data['email'] || !$data['password']) {
            return $this->response->setJSON(['error' => 'Missing credentials'])->setStatusCode(400);
        }

        $user = $this->authModel->getUserByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->response->setJSON(['error' => 'Invalid email or password'])->setStatusCode(401);
        }

        $sessionKey = getAuthSessionkey($user);

        return $this->response->setJSON([
            'user'  => $user,
            'token' => $user['auth_token']
        ]);
    }


// public function login()
// {
//     $data = json_decode($this->request->getBody(), true);

//          if (!$data['email'] || !$data['password']) {
//             return $this->response->setJSON(['error' => 'Missing credentials'])->setStatusCode(400);
//         }
// }

    // public function login()
    // {
    //     $data = $this->request->getJSON();

    //     if (!$data->email|| !$data->password) {
    //         return $this->response->setJSON(['error' => 'Missing credentials'])->setStatusCode(400);
    //     }
    //     return $this->response->setJSON([
    //         'success' => true,
    //         'message' => 'Login route works',
    //         'data' => $data,
    //         'email' => $data->email
    //     ]);
    // }

    // public function login()
    // {
    //     $data = $this->request->getJSON(true);

    //     return $this->response->setJSON([
    //         'success' => true,
    //         'data' => $data,
    //     ]);
    // }
}
