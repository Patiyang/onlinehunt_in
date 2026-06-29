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
    public function getPosts($limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                  p.pageviews, p.comment_count, p.image_url, p.created_at,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, 
                  u.avatar, u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, 
                  c.description as category_description')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1);

        // Clone builder for total count before applying limit
        if (!is_null($langId)) {
            $builder->where('p.lang_id', $langId);
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
            ]
        ];
    }

    public function getPostById($id)
    {
        return $this->select('p.*, 
                              u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar, u.cover_image, u.about_me, u.last_seen,
                              c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description')
            ->from('posts p')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.id', $id)
            ->get()
            ->getRow();
    }

    public function getPostsByCategory($categoryId, $limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                  p.pageviews, p.comment_count, p.image_url, p.created_at,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
                  u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId);

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
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
                'current_page' => floor($offset / $limit) + 1
            ]
        ];
    }




    public function getPostsBySelection($type, $limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                  p.pageviews, p.comment_count, p.image_url, p.created_at,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, 
                  u.avatar, u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, 
                  c.description as category_description')
            ->join('post_selections ps', 'ps.post_id = p.id')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('ps.selection_type', $type);

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
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
                'total'    => $total,
                'limit'    => $limit,
                'offset'   => $offset,
                'lang_id' => $langId,

                'page'     => floor($offset / $limit) + 1,
                'pages'    => ceil($total / $limit),
            ]
        ];
    }

    public function getSimilarPosts($postId, $categoryId, $langId, $limit = 10, $offset = 0)
    {
        return $this->db->table('posts p')
            ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords as keywords,
                  p.pageviews, p.comment_count, p.image_url, p.created_at,
                  u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
                  u.cover_image, u.about_me, u.last_seen,
                  c.id as category_id, c.name as category_name, c.slug as category_slug, c.description as category_description')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId)
            ->where('p.lang_id', $langId)
            ->where('p.id !=', $postId) // exclude the current post
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    public function countSimilarPosts($postId, $categoryId, $langId)
    {
        return $this->db->table('posts p')
            ->where('p.status', 1)
            ->where('p.visibility', 1)
            ->where('p.category_id', $categoryId)
            ->where('p.lang_id', $langId)
            ->where('p.id !=', $postId)
            ->countAllResults();
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
}
