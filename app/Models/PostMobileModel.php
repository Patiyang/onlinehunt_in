<?php

namespace App\Models;

use CodeIgniter\Model;

class PostMobileModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields    = [
        'pageviews',
    ];
    public function getPosts($limit = 10, $offset = 0, $langId = null, $district = null)
    {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content,p.district, p.meta_keywords,p.pageviews,p.video_id,p.video_url, p.comment_count, p.image_url, p.created_at,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description,c.meta_title, c.color,
                  f.id as feed_id, f.feed_name,f.feed_url, f.image_id as feed_image')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1);

        // Clone builder for total count before applying limit
        if (!is_null($langId)) {
            $builder->where('p.lang_id', $langId);
        }
        if (!empty($district)) {
            $builder->where('p.district', $district);
        }
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults(false); // false = don't reset query


        $posts = $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        return [
            'data' => $posts,
            'meta' => [
                'total'    => $total,
                'limit'    => $limit,
                'offset'   => $offset,
                'page'     => floor($offset / $limit) + 1,
                'pages'    => ceil($total / $limit),
                'lang_id' => $langId,
                'district' => $district,
            ]
        ];
    }

    public function getPostById($id)
    {
        return $this->select('p.*, 
                              u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,
                              c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description, c.meta_title, c.color,
                              f.id as feed_id, f.feed_name,f.feed_url,f.image_id as feed_image')
            ->from('posts p')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.id', $id)
            ->get()
            ->getRow();
    }
    public function getPostBySlug($slug)
    {
        return $this->select('p.*, 
                              u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,
                              c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description, c.meta_title, c.color,
                              f.id as feed_id, f.feed_name, f.feed_url,f.image_id as feed_image',)
            ->from('posts p')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.slug', $slug)
            ->get()
            ->getRow();
    }
    public function getPostsByCategory(
        $categoryId,
        $limit = 10,
        $offset = 0,
        $langId = null,
        $isVideo = null,
        $district = null
    ) {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                  p.pageviews,p.video_id,p.video_url, p.comment_count, p.image_url, p.created_at,p.district,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description, c.meta_title, c.color,
                  f.id as feed_id, f.feed_name, f.feed_url,f.image_id as feed_image')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId);

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
        }
        if (!empty($district)) {
            $builder->where('p.district', $district);
        }

        if ($isVideo) {
            $builder->where('p.video_id IS NOT NULL', null, false);
        }
        // count total
        $total = $builder->countAllResults(false);

        $posts = $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        return [
            'data' => $posts,
            'meta' => [
                'total'        => $total,
                'limit'        => $limit,
                'offset'       => $offset,
                'lang_id'      => $langId,
                'total_pages'  => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1,
                'district' => $district,
            ]
        ];
    }




    public function getPostsBySelection($type, $limit = 10, $offset = 0, $langId = null, $isVideo = null, $district = null)
    {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                  p.pageviews,p.video_id,p.video_url, p.comment_count, p.image_url, p.created_at,p.district,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,c.id as category_id, c.name as category_name, c.slug as category_slug, 
                  c.description as category_description, c.meta_title, c.color,
                  f.id as feed_id, f.feed_name, f.feed_url,f.image_id as feed_image')
            ->join('post_selections ps', 'ps.post_id = p.id')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('ps.selection_type', $type);

        if (!is_null($langId)) {
            $builder->where('p.lang_id', $langId);
        }
        if (!empty($district)) {
            $builder->where('p.district', $district);
        }
        if ($isVideo) {
            $builder->where('p.video_id IS NOT NULL', null, false);
        }

        $total = $builder->countAllResults(false);

        $posts = $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        return [
            'data' => $posts,
            'meta' => [
                'total'    => $total,
                'limit'    => $limit,
                'offset'   => $offset,
                'lang_id' => $langId,
                'is_video' => $isVideo,
                'page'     => floor($offset / $limit) + 1,
                'pages'    => ceil($total / $limit),
                'district' => $district,
            ]
        ];
    }

    // public function getSimilarPosts($postId, $categoryId, $langId, $limit = 10, $offset = 0)
    // {
    //     return $this->db->table('posts p')
    //         ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords as keywords,
    //               p.pageviews,p.video_id,p.video_url, p.comment_count, p.image_url, p.created_at,
    //               u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
    //               u.cover_image, u.about_me, u.last_seen,
    //               c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description, c.meta_title, c.color,
    //               f.id as feed_id, f.feed_name, f.feed_url')
    //         ->join('users u', 'u.id = p.user_id', 'left')
    //         ->join('categories c', 'c.id = p.category_id', 'left')
    //         ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
    //         ->where('p.status', 1)
    //         ->where('p.visibility', 1)
    //         ->where('p.category_id', $categoryId)
    //         ->where('p.lang_id', $langId)
    //         ->where('p.id !=', $postId) // exclude the current post
    //         ->orderBy('p.created_at', 'DESC')
    //         ->limit($limit, $offset)
    //         ->get()
    //         ->getResult();
    // }
    public function getSimilarPosts(
        $postId,
        $categoryId,
        $langId,
        $limit = 10,
        $offset = 0,
        $district = null
    ) {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords as keywords,
              p.pageviews, p.video_id, p.video_url, p.comment_count, p.image_url,
              p.created_at, p.district,
              u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
              u.cover_image, u.about_me, u.last_seen,
              c.id as category_id, c.name as category_name, c.slug as category_slug,
              c.description as category_description, c.meta_title, c.color,
              f.id as feed_id, f.feed_name, f.feed_url,f.image_id as feed_image')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId)
            ->where('p.lang_id', $langId)
            ->where('p.id !=', $postId);

        // Filter by district when supplied
        if (!empty($district)) {
            $builder->where('p.district', $district);
        }

        return $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }
    // public function countSimilarPosts($postId, $categoryId, $langId)
    // {
    //     return $this->db->table('posts p')
    //         ->where('p.status', 1)
    //         ->where('p.visibility', 1)
    //         ->where('p.category_id', $categoryId)
    //         ->where('p.lang_id', $langId)
    //         ->where('p.id !=', $postId)
    //         ->countAllResults();
    // }

    public function countSimilarPosts(
        $postId,
        $categoryId,
        $langId,
        $district = null
    ) {
        $builder = $this->db->table('posts p')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId)
            ->where('p.lang_id', $langId)
            ->where('p.id !=', $postId);

        if (!empty($district)) {
            $builder->where('p.district', $district);
        }

        return $builder->countAllResults();
    }
    public function getAuthorId($postId)
    {
        $row = $this->select('user_id')->where('id', $postId)->get()->getRow();
        return $row ? $row->user_id : null;
    }

    public function incrementPageviews($postId)
    {
        return $this->where('id', $postId)
            ->set('pageviews', 'pageviews+1', false)
            ->update();
    }

    //search

    public function searchPosts(
        string $query,
        int $limit = 10,
        int $offset = 0,
        ?int $langId = null,
        ?string $district = null,
        ?int $categoryId = null,
        ?bool $isVideo = null
    ) {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.district,
                  p.meta_keywords, p.pageviews, p.video_id, p.video_url,
                  p.comment_count, p.image_url, p.created_at,

                  u.id as user_id, u.username, u.slug as user_slug,
                  u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,

                  c.id as category_id, c.name as category_name,
                  c.slug as category_slug, c.description as category_description,
                  c.meta_title, c.color,

                  f.id as feed_id, f.feed_name, f.feed_url,f.image_id as feed_image')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('rss_feeds f', 'f.id = p.feed_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1);

        // Language
        if (!is_null($langId)) {
            $builder->where('p.lang_id', $langId);
        }

        // Search title, summary, keywords and content
        $builder->groupStart()
            ->like('p.title', $query)
            ->orLike('p.summary', $query)
            ->orLike('p.meta_keywords', $query)
            ->orLike('p.content', $query)
            ->groupEnd();

        // District filter
        if (!empty($district)) {
            $builder->where('p.district', $district);
        }

        // Category filter
        if (!is_null($categoryId)) {
            $builder->where('p.category_id', $categoryId);
        }

        // Video filter
        if (!is_null($isVideo)) {
            if ($isVideo) {
                $builder->where('p.video_id IS NOT NULL', null, false);
            } else {
                $builder->where('p.video_id IS NULL', null, false);
            }
        }

        // Get total before pagination
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        // Get results
        $posts = $builder
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        return [
            'data' => $posts,
            'meta' => [
                'total'        => $total,
                'limit'        => $limit,
                'offset'       => $offset,
                'page'         => floor($offset / $limit) + 1,
                'pages'        => ceil($total / $limit),
                'lang_id'      => $langId,
                'query'        => $query,
                'district'     => $district,
                'category_id'  => $categoryId,
                'is_video'     => $isVideo,
            ]
        ];
    }
}
