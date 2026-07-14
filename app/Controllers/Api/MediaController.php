<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MediaMobileModel;

class MediaController extends BaseController
{
    use ResponseTrait;
    protected $mediaModel;
    public function __construct()
    {
        $this->mediaModel = new MediaMobileModel();
    }
    public function index()
    {
        $page  = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $rows = $this->mediaModel->getMediaFiles($limit, $offset);
           return $this->respond([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'data'   => $rows['data'],
            // 'meta'   => $rows['meta']
        ]);
    }

    public function show($id = null)
    {

        $row = $this->mediaModel->singleFile($id);
        if (!$row) {
            return $this->failNotFound("Post with ID {$id} not found");
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $row
        ]);
    }
}
