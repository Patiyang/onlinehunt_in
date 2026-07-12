<?php

namespace App\Controllers;

use App\Models\PostMobileModel;

class DeepLinkController extends BaseController
{
    protected $postModel;
    public function __construct()
    {
        $this->postModel = new PostMobileModel();
    }

    public function show($id = null)
    {

        return "Post ID: " . $id;
        // $post = $this->postModel->getPostById($id);

        // if (!$post) {
        //     throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        // }
        // return view('deeplink/post', [
        //     'post' => $post
        // ]);
    }
    public function test()
    {
        return "TEST";
    }
}
