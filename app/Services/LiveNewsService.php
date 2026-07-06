<?php

namespace App\Services;

use Exception;
use Throwable;


class LiveNewsService {
        /**
     * @var object The model responsible for Post database operations
     */
    protected object $liveNewsModel;

    /**
     * RssService constructor
     */
    public function __construct()
    {
        $this->liveNewsModel = model('LiveNewsModel');
    }

}
