<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryMobileModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Get all categories with pagination
     */
    public function getCategories($limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('categories c')
            ->select('c.id,c.status,c.lang_id,c.meta_title,c.color, c.name, c.slug, c.description')
            ->where('c.status', 1) // only active categories
            ->orderBy('c.name', 'ASC');

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
        }
        // count total categories
        $total = $builder->countAllResults(false);

        $categories = $builder
            ->limit($limit, $offset)
            ->get()
            ->getResult();
        foreach ($categories as $category) {
            $category->id = (int)$category->id;
            $category->lang_id = (int)$category->lang_id;
            $category->status = (int)$category->status;
        }
        return [
            'data' => $categories,

            'meta' => [
                'total'       => $total,
                'limit'       => $limit,
                'offset'      => $offset,
                'lang_id' => $langId,
                'total_pages' => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ]
        ];
    }


    /**
     * Get category by ID, with posts + pagination metadata
     */
    public function getCategoryById($id, $limit = 10, $offset = 0, $langId = null, $isVideo = null)
    {
        $builder = $this->db->table('categories c')
            ->select('c.id,c.status,c.lang_id,c.meta_title,c.color, c.name, c.slug, c.description')
            ->where('c.id', $id);

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
        }

        $category = $builder->get()->getRow();
        $category->id = (int)$category->id;
        $category->lang_id = (int)$category->lang_id;
        $category->status = (int)$category->status;

        if ($category) {
            $builder = $this->db->table('posts p')
                ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                      p.pageviews, p.video_id, p.video_url, p.comment_count, p.image_url, p.created_at,p.district,
                      u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
                      u.cover_image, u.about_me, u.last_seen')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->where('p.status', 1)
                ->where('p.visibility', 1)
                ->where('p.category_id', $id)
                ->orderBy('p.created_at', 'DESC');

            if ($isVideo) {
                $builder->where('p.video_id IS NOT NULL', null, false);
            }

            // $builder->video_id = (int)$builder->video_id;
            // count total posts in category
            $total = $builder->countAllResults(false);

            $posts = $builder
                ->limit($limit, $offset)
                ->get()
                ->getResult();
            // $posts->video_id = (int)$posts[0]->video_id;
            $category->posts = $posts;
            $category->meta = [
                'total'       => $total,
                'limit'       => $limit,
                'offset'      => $offset,
                'lang_id' => $langId,
                'is_video' => $isVideo,
                'total_pages' => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ];
        }

        return $category;
    }


    /**
     * Get category by slug, with posts + pagination metadata
     */
    public function getCategoryBySlug($slug, $limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('categories c')
            ->select('c.id,c.status,c.lang_id,c.meta_title,c.color, c.name, c.slug, c.description')
            ->where('c.slug', $slug);

        if (!is_null($langId)) {
            $builder->where('c.lang_id', $langId);
        }

        $category = $builder->get()->getRow();

        // $category->id = (int)$category->id;
        // $category->lang_id = (int)$category->lang_id;
        // $category->status = (int)$category->status;

        if ($category) {
            $builder = $this->db->table('posts p')
                ->select('p.id, p.title, p.slug, p.summary, p.content, p.meta_keywords,
                      p.pageviews, p.video_id, p.video_url, p.comment_count, p.image_url, p.created_at,p.district,
                      u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
                      u.cover_image, u.about_me, u.last_seen')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->where('p.status', 1)
                ->where('p.visibility', 1)
                ->where('p.category_id', $category->id)
                ->orderBy('p.created_at', 'DESC');

            // count total posts in category
            $total = $builder->countAllResults(false);

            $posts = $builder
                ->limit($limit, $offset)
                ->get()
                ->getResult();

            $category->posts = $posts;
            $category->meta = [
                'total'       => $total,
                'limit'       => $limit,
                'offset'      => $offset,
                'lang_id' => $langId,

                'total_pages' => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ];
        }

        return $category;
    }
}
