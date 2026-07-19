<?php

namespace App\Models;

use App\Entities\Publication;

class PublicationModel extends BaseModel
{
    protected $table = 'epaper_publications';
    protected $primaryKey = 'id';
    protected $returnType = Publication::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'title',
        'slug',
        'description',
        'keywords',
        'user_id',
        'category_id',
        'lang_id',
        'publication_type',
        'website_url',
        'logo',
        'status',
        'sort_order'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Finds a publication by its slug
     */
    public function findBySlug(?string $slug, int $langId): ?object
    {
        if (empty($slug)) {
            return null;
        }

        return $this->where('slug', cleanStr($slug))
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Finds all publications by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->findAll();
    }

    /**
     * Returns paginated publications
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $this->select('
                epaper_publications.*,
                c.name AS category_name,
                (
                    SELECT COUNT(*)
                    FROM epapers
                    WHERE epapers.publication_id = epaper_publications.id
                ) AS issue_count
            ')
            ->join('categories c', 'c.id = epaper_publications.category_id', 'left');

        if (!empty($filters['lang_id'])) {
            $this->where('epaper_publications.lang_id', $filters['lang_id']);
        }

        if (!empty($filters['publication_type'])) {
            $this->where('epaper_publications.publication_type', $filters['publication_type']);
        }

        if (!empty($filters['status']) || $filters['status'] === '0') {
            $this->where('epaper_publications.status', $filters['status']);
        }

        if (!empty($filters['q'])) {
            $this->groupStart()
                ->like('epaper_publications.title', $filters['q'])
                ->orLike('epaper_publications.description', $filters['q'])
                ->groupEnd();
        }

        return $this->orderBy('epaper_publications.sort_order', 'ASC')
            ->orderBy('epaper_publications.title', 'ASC')
            ->paginate($perPage);
    }
}