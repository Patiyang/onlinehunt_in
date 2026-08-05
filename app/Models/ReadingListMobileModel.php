<?php

namespace App\Models;

use CodeIgniter\Model;

class ReadingListMobileModel extends Model
{
    protected $table = 'reading_lists';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'post_id',
        'user_id',
        'created_at'
    ];

    protected $useTimestamps = false;

    /**
     * Add a post to the reading list.
     */
    public function add(int $postId, int $userId): bool
    {
        $existing = $this->where([
            'post_id' => $postId,
            'user_id' => $userId
        ])->first();

        if ($existing) {
            return true;
        }

        return (bool)$this->insert([
            'post_id' => $postId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove a post from the reading list.
     */
    public function remove(int $postId, int $userId): bool
    {
        return (bool)$this->where([
            'post_id' => $postId,
            'user_id' => $userId
        ])->delete();
    }

    /**
     * Check whether a post is in the user's reading list.
     */
    public function exists(int $postId, int $userId): bool
    {
        return $this->where([
            'post_id' => $postId,
            'user_id' => $userId
        ])->countAllResults() > 0;
    }

    /**
     * Get the user's reading list.
     */
public function getUserReadingList(
    int $userId,
    int $limit = 10,
    int $offset = 0,
    ?int $langId = null
): array {
    $builder = $this->db->table('reading_lists rl')
        ->select('
            rl.id,
            rl.post_id,
            rl.user_id,
            rl.created_at as saved_at,

            p.title,
            p.slug,
            p.summary,
            p.content,
            p.image_url,
            p.video_url,
            p.category_id,
            p.lang_id,
            p.post_url,
            p.created_at as post_created_at,

            u.id as author_id,
            u.username as author_username,
            u.slug as author_slug,
            u.avatar as author_avatar,

            c.id as category_id,
            c.name as category_name,
            c.slug as category_slug,
        ')
        ->join('posts p', 'p.id = rl.post_id', 'inner')
        ->join('users u', 'u.id = p.user_id', 'left')
        ->join('categories c', 'c.id = p.category_id', 'left')
        ->where('rl.user_id', $userId);

    if (!is_null($langId)) {
        $builder->where('p.lang_id', $langId);
    }

    $total = $builder->countAllResults(false);

    $items = $builder
        ->orderBy('rl.created_at', 'DESC')
        ->limit($limit, $offset)
        ->get()
        ->getResult();

    foreach ($items as $item) {
        $item->id = (int)$item->id;
        $item->post_id = (int)$item->post_id;
        $item->user_id = (int)$item->user_id;
        $item->category_id = (int)$item->category_id;
        $item->lang_id = (int)$item->lang_id;
        $item->author_id = (int)$item->author_id;

        if (!empty($item->category_id)) {
            $item->category = [
                'id' => (int)$item->category_id,
                'name' => $item->category_name,
                'slug' => $item->category_slug
            ];
        } else {
            $item->category = null;
        }

        if (!empty($item->author_id)) {
            $item->author = [
                'id' => (int)$item->author_id,
                'username' => $item->author_username,
                'slug' => $item->author_slug,
                'avatar' => $item->author_avatar
            ];
        } else {
            $item->author = null;
        }

        // Remove the flattened fields
        unset(
            $item->category_name,
            $item->category_slug,
            $item->author_username,
            $item->author_slug,
            $item->author_avatar
        );
    }

    return [
        'data' => $items,
        'meta' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'lang_id' => $langId,
            'total_pages' => $limit > 0 ? ceil($total / $limit) : 0,
            'current_page' => $limit > 0
                ? floor($offset / $limit) + 1
                : 1
        ]
    ];
}
}