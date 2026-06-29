<?php

namespace App\Models;

use CodeIgniter\Model;

class PageViewModel extends Model
{
    protected $table            = 'post_pageviews_month';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'post_id',
        'post_user_id',
        'ip_address',
        'reward_amount',
        'visit_hash',
        'created_at'
    ];

    /**
     * Check if a pageview exists for a post and IP in the current month
     */
    public function hasViewed($postId, $ip)
    {
        return $this->where('post_id', $postId)
                    ->where('ip_address', $ip)
                    ->where('MONTH(created_at) = MONTH(CURRENT_DATE)', null, false)
                    ->where('YEAR(created_at) = YEAR(CURRENT_DATE)', null, false)
                    ->first();
    }

    /**
     * Insert a new pageview record
     */
    public function addView($postId, $userId, $ip, $langId = null)
    {
        return $this->insert([
            'post_id'       => $postId,
            'post_user_id'  => $userId,
            'ip_address'    => $ip,
            'lang_id'      => $langId,
            'reward_amount' => 0,
            'visit_hash'    => md5($postId . $ip . date('Ym'))
        ]);
    }
}
