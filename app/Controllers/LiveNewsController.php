<?php

namespace App\Controllers;

use App\Services\LiveNewsService;
use App\Services\FirebaseNotificationService;

class LiveNewsController extends BaseAdminController
{
    protected $liveNewsService;
    protected $liveNewsModel;
    protected $firebaseNotificationService;
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('live');

        // $this->liveNewsService = new LiveNewsService();
        $this->liveNewsModel = model('LiveNewsModel');

        $this->firebaseNotificationService =
            new FirebaseNotificationService();
    }
    public function liveNews()
    {
        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            // 'q'       => cleanStr(inputGet('q')),
        ];

        return view('admin/live-news/links', [
            'title'         => 'Live news',
            'panelSettings' => panelSettings(),
            'links'         => $this->liveNewsModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->liveNewsModel->pager
        ]);
    }

    /**
     * Live News
     */
    public function addLiveNews()
    {
        checkPermission('add_live_tv');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $postData['user_id'] = user()->id;
            $live = new \App\Entities\LiveNews();
            $live->fill($postData);
            // $feed->processForm($postData);

            if ($this->liveNewsModel->trySave($live)) {
                $imageUrl = (string)$live->image_url;

                if (
                    !empty($live->video_url) &&
                    str_contains(strtolower($live->video_url), 'youtube')
                ) {
                    $uri = parse_url($live->video_url);

                    if (!empty($uri['query'])) {
                        parse_str($uri['query'], $queryParams);

                        if (!empty($queryParams['v'])) {
                            $imageUrl = 'https://img.youtube.com/vi/'
                                . $queryParams['v']
                                . '/0.jpg';
                        }
                    }
                }
                $this->firebaseNotificationService->sendToTopic(
                    'all',
                    'Live News',
                    $live->title,
                    [
                        'type' => 'live_news',
                        'id' => (int)$live->id,
                        'live_url' => $live->url,
                        'slug' => '',
                        'image-url' => $imageUrl
                    ]
                );
                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            }
            return redirect()->to(getBackUrl())->with('errors', $this->liveNewsModel->errors());
        }

        return view('admin/live-news/form', [
            'title'          => 'Add Live News',
            'action'         => adminUrl('live-news/add'),
            'breadcrumbLink' => ['label' => 'Live News', 'url' => adminUrl('live-news')]
        ]);
    }

    public function editLiveNews($id)
    {
        checkPermission('edit_live_tv');

        $live = $this->liveNewsModel->find($id);
        if (empty($live)) {
            return redirect()->to(adminUrl('live-news'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $live->fill($postData);

            if ($this->liveNewsModel->trySave($live)) {
                $imageUrl = (string)$live->image_url;

                if (
                    !empty($live->video_url) &&
                    str_contains(strtolower($live->video_url), 'youtube')
                ) {
                    $uri = parse_url($live->video_url);

                    if (!empty($uri['query'])) {
                        parse_str($uri['query'], $queryParams);

                        if (!empty($queryParams['v'])) {
                            $imageUrl = 'https://img.youtube.com/vi/'
                                . $queryParams['v']
                                . '/0.jpg';
                        }
                    }
                }
                $this->firebaseNotificationService->sendToTopic(
                    'all',
                    'Live News',
                    $live->title,
                    [
                        'type' => 'live_news',
                        'id' => (int)$live->id,
                        'live_url' => $live->url,
                        'slug' => '',
                        'image-url' => $imageUrl
                    ]
                );
                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            }
            return redirect()->to(getBackUrl())->with('errors', $this->liveNewsModel->errors());
        }
        // Category data
        $categorySelectorData = model('CategoryModel')->selectorGetTree($live->category_id, $live->lang_id);

        return view('admin/live-news/form', [
            'title'          => 'Edit Live News',
            'action'         => adminUrl('live-news/edit/' . esc($id)),
            'categorySelectorData' => json_encode($categorySelectorData),

            'breadcrumbLink' => ['label' => 'Live News', 'url' => adminUrl('live-news')],
            'live'           => $live
        ]);
    }


    /**
     * AJAX Endpoint: Delete Feed
     *
     * @method POST
     */
    public function deleteFeed()
    {
        $id = (int)inputPost('id');

        $feed = $this->liveNewsModel->find($id);
        if (empty($feed)) {
            return jsonResponse(false);
        }

        if ($this->liveNewsModel->delete($id)) {
            setSuccessMessage("msg_deleted");
            return jsonResponse(true);
        }

        setErrorMessage("msg_error");
        return jsonResponse(false);
    }
}
