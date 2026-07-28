<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\FollowerMobileModel;

class FollowController extends BaseController
{
    protected $followerModel;

    public function __construct()
    {
        $this->followerModel = new FollowerMobileModel();
    }

    /**
     * Follow a user
     */
    public function follow()
    {
        $data = json_decode($this->request->getBody(), true);

        // return $this->response->setJSON([
        //     'following_id' => $data['following_id'] ?? null,
        //     'follower_id'  => $data['follower_id'] ?? null,
        // ]);


        if (empty($data['following_id']) || empty($data['follower_id'])) {
            return $this->response
                ->setJSON(['error' => 'Missing fields'])
                ->setStatusCode(400);
        }

        $status = $this->followerModel->follow(
            (int)$data['following_id'],
            (int)$data['follower_id']
        );

        return $this->response->setJSON([
            'success' => $status
        ]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow()
    {
        $data = json_decode($this->request->getBody(), true);

        if (empty($data['following_id']) || empty($data['follower_id'])) {
            return $this->response
                ->setJSON(['error' => 'Missing fields'])
                ->setStatusCode(400);
        }

        $status = $this->followerModel->unfollow(
            (int)$data['following_id'],
            (int)$data['follower_id']
        );

        return $this->response->setJSON([
            'success' => $status
        ]);
    }

    /**
     * Check follow status
     *
     * GET:
     * ?following_id=10&follower_id=3
     */
    public function status()
    {
        $followingId = (int)$this->request->getGet('following_id');
        $followerId  = (int)$this->request->getGet('follower_id');

        return $this->response->setJSON([
            'success' => true,
            'is_following' => $this->followerModel->isFollowing(
                $followingId,
                $followerId
            )
        ]);
    }

    /**
     * Get follower/following counts
     */
    public function counts($userId)
    {
        return $this->response->setJSON([
            'success' => true,
            'data' => $this->followerModel->getCounts((int)$userId)
        ]);
    }

    /**
     * Profile statistics
     *
     * GET:
     * ?profile_user_id=10&current_user_id=3
     */
    public function profileStats()
    {
        $profileUserId = (int)$this->request->getGet('profile_user_id');
        $currentUserId = (int)$this->request->getGet('current_user_id');

        if ($profileUserId <= 0 || $currentUserId <= 0) {
            return $this->response
                ->setJSON(['error' => 'Invalid user'])
                ->setStatusCode(400);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->followerModel->getProfileStats(
                $profileUserId,
                $currentUserId
            )
        ]);
    }
}
