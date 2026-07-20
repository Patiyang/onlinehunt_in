<?php

namespace App\Models;

use CodeIgniter\Model;


class EpaperMobileModel extends Model
{
    protected $table = 'epapers';

    /**
     * Returns issues filtered by publication type and source type.
     */
    public function getIssues(
        array $publicationTypes,
        ?string $sourceType = null,
        int $langId = 1,
        int $page = 1,
        int $limit = 20,
        ?string $frequency = null
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
        if (!empty($sourceType)) {
            $builder->where('e.source_type', $sourceType);
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
        int $limit = 20
    ): array {

        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        $builder = $this->baseQuery();

        $builder->where('e.publication_id', $publicationId);
        $builder->where('p.lang_id', $langId);

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
            e.is_today,
            e.sort_order,
            e.total_views,

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

                'pdf_file' => empty($row->pdf_file)
                    ? null
                    : $row->pdf_file,

                'is_today' => (bool)$row->is_today,

                'total_views' => (int)$row->total_views,

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
