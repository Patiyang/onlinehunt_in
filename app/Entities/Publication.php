<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Publication extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'lang_id' => 'integer',
        'category_id' => '?integer',
        'user_id' => 'integer',

        'title' => 'string',
        'slug' => 'string',
        'description' => '?string',
        'keywords' => '?string',

        'publication_type' => 'string',

        'website_url' => '?string',
        'logo' => '?string',

        'status' => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}