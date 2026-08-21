<?php

namespace App\Models;

use CodeIgniter\Model;

class MobileAdMobileModel extends Model
{
    protected $table = 'mobile_ads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Get all active mobile ads with pagination
     */
    public function getMobileAds($limit = 10, $offset = 0, $langId = null)
    {
        $builder = $this->db->table('mobile_ads ma')
            ->select('
                ma.id,
                ma.lang_id,
                ma.image,
                ma.width,
                ma.height,
                ma.ad_title,
                ma.ad_description,
                ma.button_text,
                ma.company_name,
                ma.url,
                ma.slug,
                ma.status,
                ma.created_at,
                ma.updated_at
            ')
            ->where('ma.status', 1)
            ->orderBy('ma.created_at', 'DESC');

        if (!is_null($langId)) {
            $builder->where('ma.lang_id', $langId);
        }

        // Count total ads
        $total = $builder->countAllResults(false);

        $mobileAds = $builder
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        foreach ($mobileAds as $ad) {
            $ad->id = (int) $ad->id;
            $ad->lang_id = (int) $ad->lang_id;
            $ad->width = (int) $ad->width;
            $ad->height = (int) $ad->height;
            $ad->status = (int) $ad->status;
        }

        return [
            'data' => $mobileAds,
            'meta' => [
                'total'        => $total,
                'limit'        => $limit,
                'offset'       => $offset,
                'lang_id'      => $langId,
                'total_pages'  => $limit > 0 ? ceil($total / $limit) : 0,
                'current_page' => $limit > 0
                    ? floor($offset / $limit) + 1
                    : 1
            ]
        ];
    }

    /**
     * Get a single active mobile ad by ID
     */
    public function getMobileAdById(int $id)
    {
        $ad = $this->db->table('mobile_ads ma')
            ->select('
                ma.id,
                ma.lang_id,
                ma.image,
                ma.width,
                ma.height,
                ma.ad_title,
                ma.ad_description,
                ma.button_text,
                ma.company_name,
                ma.url,
                ma.slug,
                ma.status,
                ma.created_at,
                ma.updated_at
            ')
            ->where('ma.id', $id)
            ->where('ma.status', 1)
            ->get()
            ->getRow();

        if (!$ad) {
            return null;
        }

        $ad->id = (int) $ad->id;
        $ad->lang_id = (int) $ad->lang_id;
        $ad->width = (int) $ad->width;
        $ad->height = (int) $ad->height;
        $ad->status = (int) $ad->status;

        return $ad;
    }

    /**
     * Get a single active mobile ad by slug
     */
    public function getMobileAdBySlug(string $slug,  $langId = null)
    {
        $builder = $this->db->table('mobile_ads ma')
            ->select('
                ma.id,
                ma.lang_id,
                ma.image,
                ma.width,
                ma.height,
                ma.ad_title,
                ma.ad_description,
                ma.button_text,
                ma.company_name,
                ma.url,
                ma.slug,
                ma.status,
                ma.created_at,
                ma.updated_at
            ')
            ->where('ma.slug', $slug)
            ->where('ma.status', 1);

        if (!is_null($langId)) {
            $builder->where('ma.lang_id', $langId);
        }

        $ad = $builder
            ->get()
            ->getRow();

        if (!$ad) {
            return null;
        }

        $ad->id = (int) $ad->id;
        $ad->lang_id = (int) $ad->lang_id;
        $ad->width = (int) $ad->width;
        $ad->height = (int) $ad->height;
        $ad->status = (int) $ad->status;

        return $ad;
    }
}
