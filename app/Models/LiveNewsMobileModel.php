<?php

namespace App\Models;

use CodeIgniter\Model;

class LiveNewsMobileModel extends Model
{
    protected $table = 'live_news';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Get all live news with pagination
     */
    public function getLiveNews($limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('live_news ln')
            ->select('ln.id, ln.status, ln.lang_id, ln.title,ln.url, ln.category_id, ln.description, ln.keywords, ln.created_at,
            u.id as user_id, u.username, u.slug as user_slug, u.email, u.avatar,
                      u.cover_image, u.about_me, u.last_seen')
            ->where('ln.status', 1) // only active live news
            ->orderBy('ln.created_at', 'DESC')
            ->join('users u', 'u.id = ln.user_id', 'left');

        if (!is_null($langId)) {
            $builder->where('ln.lang_id', $langId);
        }
        // count total live news
        $total = $builder->countAllResults(false);

        $liveNews = $builder
            ->limit($limit, $offset)
            ->get()
            ->getResult();
        foreach ($liveNews as $news) {
            $news->id = (int)$news->id;
            $news->lang_id = (int)$news->lang_id;
            $news->status = (int)$news->status;
            $news->category_id = (int)$news->category_id;
            $news->user_id = (int)$news->user_id;
        }
        return [
            'data' => $liveNews,
            'meta' => [
                'total'       => $total,
                'limit'       => $limit,
                'offset'      => $offset,
                'lang_id'     => $langId,
                'total_pages' => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ]
        ];
    }
}
