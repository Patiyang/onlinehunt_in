<?php

namespace App\Models;

use CodeIgniter\Model;


class EpaperMobileModel extends Model
{
    protected $table = 'epapers';


    /**
     * Get a single issue by ID.
     */
    public function getIssueById(int $issueId, int $langId): ?array
    {
        $builder = $this->db->table('epapers e');

        $builder->select("
        e.*,

        p.title AS publication_title,
        p.slug AS publication_slug,
        p.description AS publication_description,
        p.website_url,
        p.logo,
        p.logo AS publication_cover,
        p.publication_type,

        c.id AS category_id,
        c.name AS category_name,
        c.slug AS category_slug
    ");

        $builder->join(
            'epaper_publications p',
            'p.id = e.publication_id'
        );

        $builder->join(
            'categories c',
            'c.id = p.category_id',
            'left'
        );

        $builder->where('e.id', $issueId);
        $builder->where('e.status', 1);
        $builder->where('p.status', 1);
        $builder->where('p.lang_id', $langId);

        $row = $builder->get()->getRow();

        if (empty($row)) {
            return null;
        }

        return [
            'id' => (int)$row->id,
            'publication_id' => (int)$row->publication_id,

            'title' => $row->title,

            'cover_image' => empty($row->cover_image)
                ? null
                : $row->cover_image,

            'pdf_file' => empty($row->pdf_file)
                ? null
                : $row->pdf_file,

            'website_url' => $row->website_url,
            'source_type' => $row->source_type,
            'district' => $row->district,
            'issue_date' => $row->issue_date,
            'sort_order' => (int)$row->sort_order,
            'is_today' => (bool)$row->is_today,
            'is_featured' => (bool)$row->is_featured,

            'publication' => [
                'id' => (int)$row->publication_id,
                'title' => $row->publication_title,
                'slug' => $row->publication_slug,
                'description' => $row->publication_description,
                'logo' => empty($row->logo)
                    ? null
                    : $row->logo,
                'cover_image' => empty($row->publication_cover)
                    ? null
                    : $row->publication_cover,
                'publication_type' => $row->publication_type,
                // 'frequency' => $row->frequency,
            ],

            'category' => [
                'id' => (int)$row->category_id,
                'name' => $row->category_name,
                'slug' => $row->category_slug,
            ],
        ];
    }
    /**
     * Returns issues filtered by publication type and source type.
     */
    public function getIssues(
        array $publicationTypes,
        ?string $sourceType = null,
        int $langId = 1,
        int $page = 1,
        int $limit = 20,
        ?string $frequency = null,
        ?int $categoryid = null,
        ?string $district = null
    ): array {

        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        $builder = $this->baseQuery();

        $builder->whereIn('p.publication_type', $publicationTypes);
        $builder->where('p.lang_id', $langId);

        if (!empty($frequency)) {
            $builder->where('p.publication_type', $frequency);
        }
        if (!empty($categoryid)) {
            $builder->where('p.category_id', $categoryid);
        }
        if (!empty($sourceType)) {
            $builder->where('e.source_type', $sourceType);
        }
        if (!empty($district)) {
            $builder->where('e.district', $district);
        }
        $total = $builder->countAllResults(false);

        $builder->limit($limit, $offset);

        $rows = $builder->get()->getResult();

        return [
            'data' => $this->formatIssues($rows),
            'meta' => [
                'total' => $total,

                'page' => $page,
                'limit' => $limit,
                'total_pages' => (int)ceil($total / $limit)
            ]
        ];
    }

    /**
     * Returns all issues belonging to one publication.
     */
    public function getPublicationIssues(
        int $publicationId,
        int $langId = 1,
        int $page = 1,
        int $limit = 20,
        ?string $district = null
    ): array {

        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        $builder = $this->baseQuery();

        $builder->where('e.publication_id', $publicationId);
        $builder->where('p.lang_id', $langId);
        if (!empty($district)) {
            $builder->where('e.district', $district);
        }
        $total = $builder->countAllResults(false);

        $builder->limit($limit, $offset);

        $rows = $builder->get()->getResult();

        return [
            'data' => $this->formatIssues($rows),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => (int)ceil($total / $limit)
            ]
        ];
    }

    /**
     * Get magazine categories.
     */
    public function getMagazineCategories(int $langId): array
    {
        $builder = $this->db->table('categories c');

        $builder->select("
        c.id,
        c.name,
        c.slug,
        COUNT(DISTINCT p.id) AS publication_count,
        COUNT(DISTINCT e.id) AS issue_count
    ");

        $builder->join(
            'epaper_publications p',
            'p.category_id = c.id',
            'inner'
        );

        $builder->join(
            'epapers e',
            'e.publication_id = p.id AND e.status = 1',
            'left'
        );

        $builder->where('p.status', 1);
        $builder->where('p.publication_type', 'magazine');
        $builder->where('p.lang_id', $langId);

        $builder->groupBy('c.id');

        $builder->orderBy('c.name', 'ASC');

        $rows = $builder->get()->getResult();

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id' => (int)$row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'publication_count' => (int)$row->publication_count,
                'issue_count' => (int)$row->issue_count,
            ];
        }

        return [
            'data' => $data,
        ];
    }
    public function getFeaturedIssues(
        int $langId = 1,
        int $limit = 10,
        ?string $district = null
    ): array {
        $builder = $this->baseQuery();

        $builder->where('e.is_featured', 1);
        $builder->where('p.lang_id', $langId);
        
        if (!empty($district)) {
            $builder->where('e.district', $district);
        }
        $builder->limit($limit);

        return [
            'data' => $this->formatIssues(
                $builder->get()->getResult()
            )
        ];
    }
    /**
     * Base query shared by all API endpoints.
     */
    protected function baseQuery()
    {
        $builder = $this->db->table('epapers e');

        $builder->select("
            e.id,
            e.publication_id,
            e.title,
            e.cover_image,
            e.website_url,
            e.pdf_file,
            e.source_type,
            e.issue_date,
            e.district,
            e.is_today,
            e.sort_order,
            e.total_views,
            e.is_featured,

            p.title AS publication_title,
            p.slug AS publication_slug,
            p.description AS publication_description,
            p.logo,
            p.publication_type,
            p.category_id,
            p.lang_id
        ");

        $builder->join(
            'epaper_publications p',
            'p.id = e.publication_id',
            'left'
        );

        $builder->where('e.status', 1);
        $builder->where('p.status', 1);

        $builder->orderBy('e.is_today', 'DESC');
        $builder->orderBy('e.issue_date', 'DESC');
        $builder->orderBy('e.sort_order', 'ASC');

        return $builder;
    }

    /**
     * Formats rows for the Flutter application.
     */
    protected function formatIssues(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {

            $items[] = [

                'id' => (int)$row->id,
                'title' => $row->title,
                // 'description' => $row->description,

                'issue_date' => $row->issue_date,

                'cover_image' => empty($row->cover_image)
                    ? null
                    : $row->cover_image,

                'source_type' => $row->source_type,

                'website_url' => $row->website_url,

                'district' => $row->district,

                'pdf_file' => empty($row->pdf_file)
                    ? null
                    : $row->pdf_file,

                'is_today' => (bool)$row->is_today,

                'total_views' => (int)$row->total_views,

                'is_featured' => (int)$row->is_featured,
                'publication' => [

                    'id' => (int)$row->publication_id,

                    'title' => $row->publication_title,

                    'slug' => $row->publication_slug,

                    'description' => $row->publication_description,

                    'logo' => empty($row->logo)
                        ? null
                        : $row->logo,

                    'publication_type' => $row->publication_type,

                    // 'frequency' => $row->frequency,

                    'category_id' => (int)$row->category_id,

                    'lang_id' => (int)$row->lang_id
                ]
            ];
        }

        return $items;
    }
}
