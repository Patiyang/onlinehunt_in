<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PostMobileModel;
use App\Models\PageViewModel;

class PostController extends BaseController
{
    use ResponseTrait;

    protected $postMobileModel;
    protected $pageViewModel;
    // fields to exclude unless explicitly overridden
    private array $defaultExcludes = ['content', 'author.email'];

    public function __construct()
    {
        $this->postMobileModel = new PostMobileModel();
        $this->pageViewModel  = new PageViewModel();
    }

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
            $defaultImage = null;
            $defaultImgUrl = '';
            if (!empty((int)$row->feed_image)) {
                $defaultImage = model('ImageModel')->find((int)$row->feed_image);
            }
            if (!empty($defaultImage)) {
                $defaultImgUrl = getStorageFileUrl($defaultImage->image_mid, $defaultImage->storage);
            }
            $post = [
                'id'            => (int)$row->id,
                'title'         => $row->title,
                'slug'          => $row->slug,
                'summary'       => $row->summary,
                'keywords'      => $keywordsArray,
                'content'       => $row->content,
                'image_url'     => $row->image_url,
                'video_url'     => $row->video_url,
                'created_at'    => $row->created_at,
                'district'      => $row->district,
                'pageviews'     => (int)$row->pageviews,
                'feed_id'       => (int)$row->feed_id,
                'video_id'      => $row->video_id ?? null,
                'comment_count' => (int)$row->comment_count,
                'author'        => [
                    'id'          => (int)$row->user_id,
                    'username'    => $row->username,
                    'slug'        => $row->user_slug,
                    'email'       => $row->email,
                    'avatar'      => $row->avatar,
                    'cover_image' => $row->cover_image,
                    'about_me'    => $row->about_me,
                    'last_seen'   => $row->last_seen
                ],
                'category'      => [
                    'id'          => (int)$row->category_id,
                    'name'        => $row->category_name,
                    'slug'        => $row->category_slug,
                    'description' => $row->category_description,
                    'color' => $row->color,
                    'meta_title' => $row->meta_title
                ],
                'feed'      => [
                    'id'          => (int)$row->feed_id,
                    'name'        => $row->feed_name,
                    'feed_url'      => $row->feed_url,
                    'feed_image' =>$defaultImgUrl /* (int)$row->feed_image */
                ]
            ];

            // If include is defined → only keep those
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

            // Apply exclude filters
            if (!empty($exclude)) {
                foreach ($exclude as $field) {
                    if (strpos($field, '.') !== false) {
                        [$parent, $child] = explode('.', $field, 2);
                        unset($post[$parent][$child]);
                        // if parent becomes empty, remove it
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




    private function parseFilters($useDefaults = false)
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


    public function index()
    {
        $page  = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1);
        $district = $this->request->getGet('district');
        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();
        $rows = $this->postMobileModel->getPosts($limit, $offset, $lang_id, $district);
        $posts = $this->formatPosts($rows['data'], $filters);

        return $this->respond([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'data'   => $posts,
            'meta'   => $rows['meta']
        ]);
    }

    public function show($id = null)
    {
        $row = $this->postMobileModel->getPostById($id);
        if (!$row) {
            return $this->failNotFound("Post with ID {$id} not found");
        }

        $filters = $this->parseFilters(false);

        return $this->respond([
            'status' => 'success',
            'data'   => $this->formatPosts([$row], $filters)[0]
        ]);
    }
    public function showBySlug($slug = null)
    {
        $row = $this->postMobileModel->getPostBySlug($slug);
        if (!$row) {
            return $this->failNotFound("Post with slug {$slug} not found");
        }

        $filters = $this->parseFilters(false);

        return $this->respond([
            'status' => 'success',
            'data'   => $this->formatPosts([$row], $filters)[0]
        ]);
    }
    public function share($id = null)
    {

        // return "Post ID: " . $id;
        $post = $this->postMobileModel->getPostById($id);

        if (!$post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('deeplink/post', [
            'post' => $post
        ]);
    }
    public function byCategory($categoryId = null)
    {
        if (!$categoryId || !is_numeric($categoryId)) {
            return $this->failValidationError('Invalid category ID');
        }

        $page  = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1);
        $district = $this->request->getGet('district');
        $is_video = $this->request->getGet('is_video') ? (bool)$this->request->getGet('is_video') : null;
        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();
        $rows = $this->postMobileModel->getPostsByCategory($categoryId, $limit, $offset, $lang_id, $is_video, $district);
        $posts = $this->formatPosts($rows['data'], $filters);

        return $this->respond([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'data'   => $posts,
            'meta'   => $rows['meta']
        ]);
    }

    public function bySelection($type = null)
    {
        if (!$type) {
            return $this->failValidationError('Selection type is required');
        }

        $page  = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $lang_id = (int) ($this->request->getGet('lang_id') ?? 1);
        $district = $this->request->getGet('district');
        $is_video = $this->request->getGet('is_video') ? (bool)$this->request->getGet('is_video') : null;

        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();
        $rows = $this->postMobileModel->getPostsBySelection($type, $limit, $offset, $lang_id, $is_video, $district);
        $posts = $this->formatPosts($rows['data'], $filters);

        return $this->respond([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'data'   => $posts,
            'meta'   => $rows['meta']

        ]);
    }

    public function similar($id)
    {
        $limit  = (int) $this->request->getGet('limit') ?: 10;
        $offset = (int) $this->request->getGet('offset') ?: 0;

        // Get the current post
        $currentPost = $this->postMobileModel->getPostById($id);
        if (!$currentPost) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Post not found'
            ])->setStatusCode(404);
        }

        // Get total similar posts for pagination
        $total = $this->postMobileModel->countSimilarPosts(
            $id,
            $currentPost->category_id,
            $currentPost->lang_id
        );

        // Get paginated similar posts
        $rows = $this->postMobileModel->getSimilarPosts(
            $id,
            $currentPost->category_id,
            $currentPost->lang_id,
            $limit,
            $offset,
            $currentPost->district
        );

        // Apply filters
        $options = $this->parseFilters();
        $posts   = $this->formatPosts($rows, $options);

        return $this->response->setJSON([

            'meta' => [
                'total'        => $total,
                'limit'        => $limit,
                'offset'       => $offset,
                'total_pages'  => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ],
            'data' => $posts,
        ]);
    }


    // In app/Controllers/Api/PostController.php

    public function addPageview($postId = null)
    {
        if (!$postId || !is_numeric($postId)) {
            return $this->failValidationError('Invalid post ID');
        }

        $ip = $this->request->getIPAddress();

        if ($this->pageViewModel->hasViewed($postId, $ip)) {
            return $this->respond([
                'status'  => 'skipped',
                'message' => 'Already counted this month for this IP'
            ]);
        }

        $authorId = $this->postMobileModel->getAuthorId($postId);

        $this->pageViewModel->addView($postId, $authorId, $ip);
        $this->postMobileModel->incrementPageviews($postId);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Pageview added'
        ]);
    }

    public function search()
    {
        $query = trim((string)$this->request->getGet('q'));

        // Minimum search length
        if (mb_strlen($query) < 3) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Search query must be at least 3 characters long'
            ], 400);
        }

        $page = max(
            1,
            (int)$this->request->getGet('page')
        );

        $limit = max(
            1,
            (int)$this->request->getGet('limit')
        );

        $lang_id = (int)(
            $this->request->getGet('lang_id') ?? 1
        );

        $district = trim(
            (string)$this->request->getGet('district')
        );

        $district = $district === ''
            ? null
            : $district;

        $categoryId = $this->request->getGet('category_id');

        $categoryId = (
            $categoryId !== null &&
            $categoryId !== ''
        )
            ? (int)$categoryId
            : null;

        $isVideoParam = $this->request->getGet('is_video');

        $isVideo = null;

        if ($isVideoParam !== null) {
            $isVideo = filter_var(
                $isVideoParam,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        $offset = ($page - 1) * $limit;

        $filters = $this->parseFilters();

        $rows = $this->postMobileModel->searchPosts(
            $query,
            $limit,
            $offset,
            $lang_id,
            $district,
            $categoryId,
            $isVideo
        );

        $posts = $this->formatPosts(
            $rows['data'],
            $filters
        );

        return $this->respond([
            'status' => 'success',
            'page'   => $page,
            'limit'  => $limit,
            'data'   => $posts,
            'meta'   => $rows['meta']
        ]);
    }
}
