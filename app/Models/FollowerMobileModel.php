<?php

namespace App\Models;

class FollowerMobileModel extends BaseModel
{
    protected $table      = 'followers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'following_id',
        'follower_id'
    ];

    protected $useTimestamps = false;

    /**
     * Follow a user
     */
    public function follow(int $followingId, int $followerId): bool
    {
        if ($followingId == $followerId) {
            return false;
        }

        $exists = $this->where([
            'following_id' => $followingId,
            'follower_id'  => $followerId
        ])->first();

        if ($exists) {
            return true;
        }

        return (bool)$this->insert([
            'following_id' => $followingId,
            'follower_id'  => $followerId
        ]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow(int $followingId, int $followerId): bool
    {
        return $this->where([
            'following_id' => $followingId,
            'follower_id'  => $followerId
        ])->delete();
    }

    /**
     * Check if follower follows user
     */
    public function isFollowing(int $followingId, int $followerId): bool
    {
        return $this->where([
            'following_id' => $followingId,
            'follower_id'  => $followerId
        ])->countAllResults() > 0;
    }

    /**
     * Get follower count
     */
    public function getFollowerCount(int $userId): int
    {
        return $this->where('following_id', $userId)
            ->countAllResults();
    }

    /**
     * Get following count
     */
    public function getFollowingCount(int $userId): int
    {
        return $this->where('follower_id', $userId)
            ->countAllResults();
    }

    /**
     * Get both counts
     */
    public function getCounts(int $userId): array
    {
        return [
            'followers' => $this->getFollowerCount($userId),
            'following' => $this->getFollowingCount($userId),
        ];
    }


    /**
     * Get profile follow statistics
     */
    public function getProfileStats(int $profileUserId, int $currentUserId): array
    {
        return [
            'followers' => $this->getFollowerCount($profileUserId),
            'following' => $this->getFollowingCount($profileUserId),
            'is_following' => $this->isFollowing($profileUserId, $currentUserId)
        ];
    }
}
