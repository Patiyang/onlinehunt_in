<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MobileAd extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'lang_id' => 'integer',

        'image' => '?string',

        'width' => 'integer',
        'height' => 'integer',

        'enabled' => 'boolean',

        'ad_description' => '?string',
        'button_text' => '?string',
        'ad_title' => '?string',
        'slug' => '?string',
        'company_name' => '?string',
        'url' => '?string',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}