<?php

namespace App\Controllers\Api;

use App\Models\LiveNewsMobileModel;
use CodeIgniter\RESTful\ResourceController;

class LiveNewsController extends ResourceController
{
    protected $liveNewsMobileModel;
    private array $defaultExcludes = ['description', 'keywords'];

    public function __construct()
    {
        $this->liveNewsMobileModel = new LiveNewsMobileModel();
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
}
