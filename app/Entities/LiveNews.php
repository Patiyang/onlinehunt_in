<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class LiveNews extends Entity
{
    protected $casts = [
        'id'             => 'integer',
        'title'           => 'string',
        'url'            => 'string',
        'description'    => 'string',
        'keywords'       => 'string',
        'user_id'        => 'integer',
        'category_id'    => 'string',
        'lang_id'        => 'integer',
        'status'         => 'boolean',
        'last_checked'   => 'datetime',
        'last_success'   => 'datetime',
        'check_interval' => 'integer',

        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // public function setKeywords($value)
    // {
    //     if (is_array($value)) {
    //         $this->attributes['keywords'] = implode(',', $value);
    //         return;
    //     }

    //     $this->attributes['keywords'] = $value;
    // }

    // public function getKeywords()
    // {
    //     if (empty($this->attributes['keywords'])) {
    //         return [];
    //     }

    //     return array_map('trim', explode(',', $this->attributes['keywords']));
    // }
}
