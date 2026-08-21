<?php

namespace App\Models;

use App\Entities\MobileAd;

class MobileAdModel extends BaseModel
{
    protected $table      = 'mobile_ads';
    protected $primaryKey = 'id';
    protected $returnType = MobileAd::class;

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'lang_id',
        'image',
        'width',
        'height',
        'ad_title',
        'ad_description',
        'button_text',
        'company_name',
        'url',
        'slug',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get enabled ads for a language.
     */
    public function getActiveAds(int $langId = 1): array
    {
        return $this->where('lang_id', $langId)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Get a single ad by slug.
     */
    public function getBySlug(string $slug, int $langId = 1)
    {
        return $this->where('slug', $slug)
            ->where('lang_id', $langId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Get a single ad by ID.
     */
    public function getById(int $id)
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Finds all ads by language.
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)
            ->findAll();
    }

    /**
     * Finds all ads with pagination and optional filters.
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('mobile_ads.*');

        if (!empty($filters['lang_id'])) {
            $builder->where('mobile_ads.lang_id', $filters['lang_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $builder->where('mobile_ads.status', $filters['status']);
        }

        if (!empty($filters['q'])) {
            $builder->like('mobile_ads.ad_title', $filters['q']);
        }

        return $builder
            ->orderBy('mobile_ads.id', 'DESC')
            ->paginate($perPage);
    }
}