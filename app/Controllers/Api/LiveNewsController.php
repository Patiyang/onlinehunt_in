<?php

namespace App\Controllers\Api;

use App\Models\LiveNewsMobileModel;
use CodeIgniter\RESTful\ResourceController;

use App\Services\FirebaseNotificationService;

class LiveNewsController extends ResourceController
{
    protected $liveNewsMobileModel;
    protected $firebaseNotificationService;

    private array $defaultExcludes = ['description', 'keywords'];

    public function __construct()
    {
        $this->liveNewsMobileModel = new LiveNewsMobileModel();
        $this->firebaseNotificationService = new FirebaseNotificationService();
    }



    public function index()
    {
        $limit = (int)$this->request->getGet('limit') ?: 10;
        $offset = (int)$this->request->getGet('offset') ?: 0;
        $langId = (int)$this->request->getGet('lang_id') ?: null;

        // $filters = $this->parseFilters();

        $result = $this->liveNewsMobileModel->getLiveNews($limit, $offset, $langId);

        return $this->respond([
            'status' => 200,
            'message' => 'Live news fetched successfully',
            'data' => $result['data'],
            'meta' => $result['meta']
        ]);
    }


    public function showOne($id)
    {
        $news = $this->liveNewsMobileModel->getLiveNewsById((int)$id);

        if (!$news) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Live news item not found.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $news
        ]);
    }

    // public function testNotification()
    // {
    //     $postData =json_decode($this->request->getBody(), true);
    //     $this->firebaseNotificationService->sendToTopic(
    //         'all',
    //         $postData['title'],
    //         $postData['body'],
    //         [
    //             'type' => $postData['type'],
    //             'image_url' => $postData['image_url'],
    //             'slug' => $postData['slug'],
    //             'id' => (int)$postData['id'],
    //             'live_url' => $postData['live_url'],

    //         ]
    //     );
    // }

    public function testNotification()
    {
        $postData = json_decode($this->request->getBody(), true);

        if (empty($postData['title']) || empty($postData['body'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Title and body are required.'
                ])
                ->setStatusCode(400);
        }

        try {
            $result = $this->firebaseNotificationService->sendToTopic(
                'all',
                $postData['title'],
                $postData['body'],
                [
                    'type'      => (string)($postData['type'] ?? ''),
                    'image_url' => (string)($postData['image_url'] ?? ''),
                    'slug'      => (string)($postData['slug'] ?? ''),
                    'id'        => (string)($postData['id'] ?? ''),
                    'live_url'  => (string)($postData['live_url'] ?? ''),
                ]
            );

            if (!empty($result['success'])) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Notification sent successfully.',
                    'firebase' => $result
                ]);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Firebase failed to send the notification.',
                    'firebase' => $result
                ])
                ->setStatusCode(500);
        } catch (\Throwable $e) {

            log_message(
                'error',
                'Test notification failed: ' . $e->getMessage()
            );

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while sending the notification.',
                    'error' => $e->getMessage()
                ])
                ->setStatusCode(500);
        }
    }
}
