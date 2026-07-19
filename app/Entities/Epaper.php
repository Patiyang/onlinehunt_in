<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Epaper extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'publication_id' => 'integer',
        'user_id' => 'integer',

        'title' => '?string',

        // 'issue_date' => 'date',

        'source_type' => 'string',

        'pdf_file' => '?string',
        'website_url' => '?string',
        'cover_image' => '?string',

        'is_featured' => 'boolean',
        'is_today' => 'boolean',
        'status' => 'boolean',

        'total_views' => 'integer',
        'sort_order' => 'integer',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}