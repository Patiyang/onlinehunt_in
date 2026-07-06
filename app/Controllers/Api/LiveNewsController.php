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

    private function parseFilters($useDefaults = true)
    {
        $include = $this->request->getGet('include');
        $exclude = $this->request->getGet('exclude');

        $include = $include ? explode(',', $include) : [];
        $exclude = $exclude ? explode(',', $exclude) : [];

        if ($useDefaults && empty($include)) {
            $exclude = array_unique(array_merge($this->defaultExcludes, $exclude));
        }

        return [
            'include' => $include,
            'exclude' => $exclude
        ];
    }

    private function formatLiveNews($rows, array $options = [])
    {
        $liveNews = [];

        $include = $options['include'] ?? [];
        $exclude = $options['exclude'] ?? [];

        foreach ($rows as $row) {
            $keywordsArray = [];
            if (!empty($row->keywords)) {
                $keywordsArray = array_map('trim', explode(',', $row->keywords));
            }

            $newsItem = [
                'id'            => (int)$row->id,
                'title'         => $row->title,
                'url'           => $row->url,
                'created_at'    => $row->created_at,
            ];

            // Apply include filters
            if (!empty($include)) {
                $filtered = [];
                foreach ($include as $field) {
                    if (isset($newsItem[$field])) {
                        $filtered[$field] = $newsItem[$field];
                    }
                }
                $newsItem = $filtered;
            }

            // Apply exclude filters
            if (!empty($exclude)) {
                foreach ($exclude as $field) {
                    unset($newsItem[$field]);
                }
            }

            $liveNews[] = $newsItem;
        }

        return $liveNews;
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
}
