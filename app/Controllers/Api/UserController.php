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
            // return $this->failValidationError('No fields to update');
              return $this->respond([
                'status'  => 'error',
                'message' => 'No fields to update'
            ], 400);
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

    //profile upload

    public function uploadProfileImage()
    {
        // Get authenticated user
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get uploaded file
        $file = $this->request->getFile('image');

        if (!$file || !$file->isValid()) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'No valid image was uploaded'
            ], 400);
        }

        // Validate image type
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($file->getMimeType(), $allowedTypes, true)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Only JPG, PNG and WebP images are allowed'
            ], 400);
        }

        // Optional: 5 MB maximum
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Image must not exceed 5 MB'
            ], 400);
        }

        // Current year/month
        $folder = date('Ym');

        // uploads/profile/202607
        $uploadPath = FCPATH . 'uploads/profile/' . $folder;

        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Unable to create upload directory'
                ], 500);
            }
        }

        // Generate unique filename
        $extension = $file->getExtension();

        $filename = 'profile_' .
            bin2hex(random_bytes(16)) .
            '.' . $extension;

        // Move file
        if (!$file->move($uploadPath, $filename)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Failed to upload image'
            ], 500);
        }

        // Relative path stored in database
        $relativePath = 'uploads/profile/' . $folder . '/' . $filename;

        return $this->respond([
            'status' => 'success',
            'message' => 'Profile image uploaded successfully',
            'data' => [
                'image' => $relativePath,
                'url'   => base_url($relativePath)
            ]
        ]);
    }
}
