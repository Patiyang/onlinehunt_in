<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ReactionMobileModel;

class ReactionController extends BaseController
{
    protected $reactionModel;

    public function __construct()
    {
        $this->reactionModel = new ReactionMobileModel();
    }

    /**
     * Add or update a reaction
     *
     * POST /api/reactions
     */
    public function react()
    {
        $data = json_decode($this->request->getBody(), true);

        if (
            empty($data['post_id']) ||
            empty($data['user_id']) ||
            empty($data['reaction'])
        ) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Missing required fields.'
                ])
                ->setStatusCode(400);
        }

        $success = $this->reactionModel->react(
            (int)$data['post_id'],
            (int)$data['user_id'],
            trim($data['reaction'])
        );

        return $this->response->setJSON([
            'success' => $success
        ]);
    }

    /**
     * Remove a reaction
     *
     * POST /api/reactions/remove
     */
    public function remove()
    {
        $data = json_decode($this->request->getBody(), true);

        if (
            empty($data['post_id']) ||
            empty($data['user_id'])
        ) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Missing required fields.'
                ])
                ->setStatusCode(400);
        }

        $success = $this->reactionModel->removeReaction(
            (int)$data['post_id'],
            (int)$data['user_id']
        );

        return $this->response->setJSON([
            'success' => $success
        ]);
    }

    /**
     * Get all reaction totals for a post
     *
     * GET /api/posts/{id}/reactions
     */
    public function summary($postId)
    {
        return $this->response->setJSON([
            'success' => true,
            'data' => $this->reactionModel->getReactionSummary((int)$postId)
        ]);
    }

    /**
     * Get a user's reaction for a post
     *
     * GET /api/reactions/user?post_id=1&user_id=2
     */
    public function userReaction()
    {
        $postId = (int)$this->request->getGet('post_id');
        $userId = (int)$this->request->getGet('user_id');

        if ($postId <= 0 || $userId <= 0) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid request.'
                ])
                ->setStatusCode(400);
        }

        return $this->response->setJSON([
            'success' => true,
            'reaction' => $this->reactionModel->getUserReaction(
                $postId,
                $userId
            )
        ]);
    }
}