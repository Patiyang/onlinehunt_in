<?php

namespace App\Controllers\Api;

use App\Models\CategoryMobileModel;
use CodeIgniter\RESTful\ResourceController;

class CategoryController extends ResourceController
{
    protected $categoryMobileModel;
    private array $defaultExcludes = ['content', 'keywords'];

    public function __construct()
    {
        $this->categoryMobileModel = new CategoryMobileModel();
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

    /**
     * Format posts with include/exclude filtering
     */
    private function formatPosts($rows, array $options = [])
    {
        $posts = [];

        $include = $options['include'] ?? [];
        $exclude = $options['exclude'] ?? [];

        foreach ($rows as $row) {
            $keywordsArray = [];
            if (!empty($row->keywords)) {
                $keywordsArray = array_map('trim', explode(',', $row->keywords));
            }

            $post = [
                'id'            => (int)$row->id,
                'title'         => $row->title,
                'slug'          => $row->slug,
                'summary'       => $row->summary,
                'keywords'      => $keywordsArray,
                'content'       => $row->content,
                'image_url'     => $row->image_url,
                'created_at'    => $row->created_at,
                'pageviews'     => (int)$row->pageviews,
                'video_id'      => $row->video_id == null ? null : (int)$row->video_id,
                'video_url'     => $row->video_url ?? null,
                'comment_count' => (int)$row->comment_count,
                'author'        => [
                    'id'          => (int)$row->user_id,
                    'username'    => $row->username,
                    'slug'        => $row->user_slug,
                    'email'       => $row->email,
                    'avatar'      => $row->avatar,
                    'cover_image' => $row->cover_image,
                    'about_me'    => $row->about_me,
                    'last_seen'   => $row->last_seen,
                ],

            ];

            // Handle include filtering
            if (!empty($include)) {
                $filtered = [];
                foreach ($include as $field) {
                    if (strpos($field, '.') !== false) {
                        [$parent, $child] = explode('.', $field, 2);
                        if (isset($post[$parent][$child])) {
                            $filtered[$parent][$child] = $post[$parent][$child];
                        }
                    } elseif (isset($post[$field])) {
                        $filtered[$field] = $post[$field];
                    }
                }
                $post = $filtered;
            }

            // Handle exclude filtering
            if (!empty($exclude)) {
                foreach ($exclude as $field) {
                    if (strpos($field, '.') !== false) {
                        [$parent, $child] = explode('.', $field, 2);
                        unset($post[$parent][$child]);
                        if (isset($post[$parent]) && empty($post[$parent])) {
                            unset($post[$parent]);
                        }
                    } else {
                        unset($post[$field]);
                    }
                }
            }

            $posts[] = $post;
        }

        return $posts;
    }


    /**
     * List all categories (paginated)
     * Example: /api/categories?limit=10&page=2
     */
    public function index()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1); // Default to English if not provided
        $offset = ($page - 1) * $limit;

        $categories = $this->categoryMobileModel->getCategories($limit, $offset, $lang_id);

        return $this->response->setJSON($categories);
    }

    /**
     * Get category by ID with posts (paginated)
     * Example: /api/categories/1?limit=5&page=2
     */
    public function show($id = null)
    {
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1);
        $is_video = $this->request->getGet('is_video') ? (bool)$this->request->getGet('is_video') : null;
        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();

        $category = $this->categoryMobileModel->getCategoryById($id, $limit, $offset, $lang_id, $is_video);

        if (!$category) {
            return $this->failNotFound('Category not found');
        }
        if (!empty($category->posts)) {
            $category->posts = $this->formatPosts($category->posts, $filters);
        }
        return $this->response->setJSON($category);
    }

    /**
     * Get category by slug with posts (paginated)
     * Example: /api/categories/slug/sports?limit=5&page=3
     */
    public function bySlug($slug = null)
    {
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1);
        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();

        $category = $this->categoryMobileModel->getCategoryBySlug($slug, $limit, $offset, $lang_id);

        if (!$category) {
            return $this->failNotFound('Category not found');
        }

        // Apply formatting on posts if they exist
        if (!empty($category->posts)) {
            $category->posts = $this->formatPosts($category->posts, $filters);
        }

        return $this->response->setJSON($category);
    }
}
