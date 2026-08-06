<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserMobileModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    protected $userMobileModel;

    public function __construct()
    {
        $this->userMobileModel = new UserMobileModel();
    }

    /**
     * Get logged-in user's profile
     */
    public function profile()
    {
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return $this->failUnauthorized('Invalid or missing token');
        }

        $user = $this->userMobileModel->getUserProfile($userId);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $user
        ]);
    }


    /**
     * Update logged-in user's profile
     */
    public function update()
    {
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return $this->failUnauthorized('Invalid or missing token');
        }

        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->failValidationError('Invalid request data');
        }

        $updateData = [];

        if (array_key_exists('email', $data)) {
            $updateData['email'] = trim($data['email']);
        }

        if (array_key_exists('first_name', $data)) {
            $updateData['first_name'] = trim($data['first_name']);
        }

        if (array_key_exists('last_name', $data)) {
            $updateData['last_name'] = trim($data['last_name']);
        }

        if (array_key_exists('about_me', $data)) {
            $updateData['about_me'] = trim($data['about_me']);
        }

        if (array_key_exists('avatar', $data)) {
            $updateData['avatar'] = $data['avatar'];
        }

        if (empty($updateData)) {
            return $this->failValidationError('No fields to update');
        }

        $updated = $this->userMobileModel->updateUserProfile(
            $userId,
            $updateData
        );

        if (!$updated) {
            return $this->failServerError('Unable to update profile');
        }

        $user = $this->userMobileModel->getUserProfile($userId);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'data'    => $user
        ]);
    }


    /**
     * Get user ID from Bearer token
     */
    private function getAuthenticatedUserId()
    {
        $header = $this->request->getHeaderLine('Authorization');

        if (!$header) {
            return null;
        }

        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return null;
        }

        $token = trim($matches[1]);

        if (!$token) {
            return null;
        }

        // TODO:
        // Validate the token using the same authentication
        // mechanism used by AuthController.

        $authModel = model('AuthMobileModel');

        $user = $authModel->where('auth_token', $token)->first();

        return $user['id'] ?? null;
    }
}