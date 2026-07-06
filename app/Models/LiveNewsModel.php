<?php

namespace App\Models;
use App\Entities\LiveNews;

class LiveNewsModel extends BaseModel
{
    protected $table      = 'live_news';
    protected $primaryKey = 'id';
    protected $returnType = LiveNews::class;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'url',
        'description',
        'keywords',
        'user_id',
        'category_id',
        'lang_id',
        'status',
        'last_checked',
        'last_success',
        'check_interval'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    /**
     * Finds a single post by its slug
     */
    public function findBySlug(?string $slug, int $langId): ?object
    {
        if (empty($slug)) {
            return null;
        }

        $post = $this->dataBuilder($langId, true)
            ->where('posts.slug', cleanStr($slug))
            ->first();

        return $this->hydrateImagesToPosts($post);
    }
      /**
     * Finds all albums by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)
            ->findAll();
    }
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('live_news.*, c.name as category_name,
            (SELECT COUNT(*) FROM posts WHERE posts.feed_id = live_news.id) as post_count')
            ->join('categories c', 'c.id = live_news.category_id', 'left');

        if (!empty($filters['lang_id'])) {
            $this->where('live_news.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['q'])) {
            $this->like('live_news.title', $filters['q']);
        }

        return $this->orderBy('live_news.id DESC')
            ->paginate($perPage);
    }
}