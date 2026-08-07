<?php

namespace App\Models;

use App\Entities\Epaper;

class EpaperModel extends BaseModel
{
    protected $table = 'epapers';
    protected $primaryKey = 'id';
    protected $returnType = Epaper::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'publication_id',
        'user_id',
        'title',
        'issue_date',
        'source_type',
        'pdf_file',
        'website_url',
        'cover_image',
        'is_featured',
        'district',
        'status',
        'total_views',
        'sort_order',
        'is_today'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Finds a single epaper by ID
     */
    public function findById(int $id): ?object
    {
        return $this->find($id);
    }

    /**
     * Finds all issues for a publication
     */
    public function findByPublication(int $publicationId): array
    {
        return $this->where('publication_id', $publicationId)
            ->orderBy('issue_date', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Returns paginated issues
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $this->select('
                epapers.*,
                epaper_publications.title AS publication_title,
                epaper_publications.lang_id,
                epaper_publications.publication_type
            ')
            ->join(
                'epaper_publications',
                'epaper_publications.id = epapers.publication_id',
                'left'
            );

        if (!empty($filters['publication_id'])) {
            $this->where('epapers.publication_id', $filters['publication_id']);
        }

        if (!empty($filters['lang_id'])) {
            $this->where('epaper_publications.lang_id', $filters['lang_id']);
        }

        if (!empty($filters['publication_type'])) {
            $this->where('epaper_publications.publication_type', $filters['publication_type']);
        }

        if (!empty($filters['source_type'])) {
            $this->where('epapers.source_type', $filters['source_type']);
        }

        // if (!empty($filters['status']) || $filters['status'] === '0') {
        //     $this->where('epapers.status', $filters['status']);
        // }

        if (!empty($filters['q'])) {
            $this->groupStart()
                ->like('epaper_publications.title', $filters['q'])
                ->orLike('epapers.title', $filters['q'])
                ->groupEnd();
        }

        return $this->orderBy('epapers.issue_date', 'DESC')
            ->orderBy('epapers.sort_order', 'ASC')
            ->paginate($perPage);
    }

    /**
     * Returns the latest published issues
     */
    public function getLatest(int $limit = 20): array
    {
        return $this->select('
                epapers.*,
                epaper_publications.title AS publication_title
            ')
            ->join(
                'epaper_publications',
                'epaper_publications.id = epapers.publication_id',
                'left'
            )
            ->where('epapers.status', 1)
            ->orderBy('epapers.issue_date', 'DESC')
            ->orderBy('epapers.sort_order', 'ASC')
            ->findAll($limit);
    }

    /**
     * Returns featured issues
     */
    public function getFeatured(int $limit = 10): array
    {
        return $this->select('
                epapers.*,
                epaper_publications.title AS publication_title
            ')
            ->join(
                'epaper_publications',
                'epaper_publications.id = epapers.publication_id',
                'left'
            )
            ->where('epapers.status', 1)
            ->where('epapers.is_featured', 1)
            ->orderBy('epapers.issue_date', 'DESC')
            ->orderBy('epapers.sort_order', 'ASC')
            ->findAll($limit);
    }
}