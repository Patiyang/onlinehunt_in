<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ReadingListMobileModel;

class ReadingListController extends BaseController
{
    protected $readingListModel;

    public function __construct()
    {
        $this->readingListModel = new ReadingListMobileModel();
    }

    /**
     * Add a post to the reading list.
     *
     * POST /api/reading-list
     */
    public function add()
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

        $success = $this->readingListModel->add(
            (int)$data['post_id'],
            (int)$data['user_id']
        );

        return $this->response->setJSON([
            'success' => $success,
            'message' => $success
                ? 'Post added to reading list.'
                : 'Unable to add post to reading list.'
        ]);
    }

    /**
     * Remove a post from the reading list.
     *
     * POST /api/reading-list/remove
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

        $success = $this->readingListModel->remove(
            (int)$data['post_id'],
            (int)$data['user_id']
        );

        return $this->response->setJSON([
            'success' => $success,
            'message' => $success
                ? 'Post removed from reading list.'
                : 'Unable to remove post.'
        ]);
    }

    /**
     * Check if a post is saved.
     *
     * GET /api/reading-list/status
     */
    public function status()
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
            'saved' => $this->readingListModel->exists(
                $postId,
                $userId
            )
        ]);
    }

    /**
     * Get user's reading list.
     *
     * GET /api/reading-list
     */
    public function index()
    {
        $userId = (int)$this->request->getGet('user_id');
        $limit = (int)$this->request->getGet('limit') ?: 10;
        $page = (int)$this->request->getGet('page') ?: 1;
        $langId = $this->request->getGet('lang_id');

        $limit = min($limit, 50);
        $page = max($page, 1);

        $offset = ($page - 1) * $limit;

        $result = $this->readingListModel->getUserReadingList(
            $userId,
            $limit,
            $offset,
            $langId !== null ? (int)$langId : null
        );

        return $this->response->setJSON([
            'success' => true,
            ...$result
        ]);
    }
}