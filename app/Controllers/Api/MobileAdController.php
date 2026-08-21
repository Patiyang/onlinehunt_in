<?php

namespace App\Controllers\Api;

use App\Models\MobileAdMobileModel;
use CodeIgniter\RESTful\ResourceController;

class MobileAdController extends ResourceController
{
    protected $mobileAdMobileModel;

    public function __construct()
    {
        $this->mobileAdMobileModel = new MobileAdMobileModel();
    }

    /**
     * Get all active mobile ads.
     *
     * Optional query parameters:
     * ?limit=10
     * ?offset=0
     * ?lang_id=1
     */
    public function index()
    {
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $offset = (int) $this->request->getGet('offset') ?: 0;
        $langId = (int) $this->request->getGet('lang_id') ?: null;

        $result = $this->mobileAdMobileModel->getMobileAds(
            $limit,
            $offset,
            $langId
        );

        return $this->respond([
            'status'  => 200,
            'message' => 'Mobile ads fetched successfully',
            'data'    => $result['data'],
            'meta'    => $result['meta']
        ]);
    }

    /**
     * Get a single active mobile ad by ID.
     */
    public function showOne($id)
    {
        $ad = $this->mobileAdMobileModel->getMobileAdById(
            (int) $id
        );

        if (!$ad) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Mobile ad not found.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $ad
        ]);
    }

    /**
     * Get a single active mobile ad by slug.
     *
     * Optional query parameter:
     * ?lang_id=1
     */
    public function showBySlug($slug)
    {
        $langId = (int) $this->request->getGet('lang_id') ?: null;

        $ad = $this->mobileAdMobileModel->getMobileAdBySlug(
            cleanStr($slug),
            $langId
        );

        if (!$ad) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Mobile ad not found.'
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $ad
        ]);
    }
}