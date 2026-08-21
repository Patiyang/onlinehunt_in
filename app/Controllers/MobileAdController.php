<?php

namespace App\Controllers;

use App\Entities\MobileAd;

class MobileAdController extends BaseAdminController
{
    protected $mobileAdModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        checkPermission('mobile_ad');

        $this->mobileAdModel = model('MobileAdModel');
    }

    /**
     * Mobile Ads
     */
    public function mobileAds()
    {
        $filters = [
            'lang_id' => (int) inputGet('lang_id'),
            'status'  => inputGet('status'),
            'q'       => cleanStr(inputGet('q')),
        ];

        return view('admin/mobile_ads/mobile_ads', [
            'title'        => 'Mobile Ads',
            'panelSettings' => panelSettings(),
            'ads'          => $this->mobileAdModel->findAllPaginated(
                $filters,
                $this->perPage
            ),
            'pager'        => $this->mobileAdModel->pager,
        ]);
    }

    /**
     * Add Mobile Ad
     */
    public function addMobileAd()
    {
        checkPermission('add_mobile_ad');

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            $postData['lang_id'] = (int) ($postData['lang_id'] ?? 1);

            // Generate slug if empty
            if (empty($postData['slug']) && !empty($postData['ad_title'])) {
                $postData['slug'] = strSlug($postData['ad_title']);
            }

            // Upload ad image
            $image = $this->uploadFile('image', 'mobile_ads');

            if ($image !== null) {
                $postData['image'] = $image;
            }

            $mobileAd = new MobileAd();
            $mobileAd->fill($postData);

            if ($this->mobileAdModel->trySave($mobileAd)) {

                setSuccessMessage("msg_added");

                return redirect()->to(getBackUrl());
            }

            return redirect()->to(getBackUrl())
                ->withInput()
                ->with('errors', $this->mobileAdModel->errors());
        }

        return view('admin/mobile_ads/form', [
            'title'          => 'Add Mobile Ad',
            'action'         => adminUrl('mobile-ads/add'),
            'breadcrumbLink' => [
                'label' => 'Mobile Ads',
                'url'   => adminUrl('mobile-ads')
            ]
        ]);
    }

    /**
     * Edit Mobile Ad
     */
    public function editMobileAd($id)
    {
        checkPermission('edit_mobile_ad');

        $mobileAd = $this->mobileAdModel->find($id);

        if (empty($mobileAd)) {
            return redirect()->to(adminUrl('mobile-ads'));
        }

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            // Upload new image if selected
            $image = $this->uploadFile('image', 'mobile_ads');

            if ($image !== null) {

                // Delete old image
                if (!empty($mobileAd->image)) {

                    $oldFile = FCPATH . $mobileAd->image;

                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $postData['image'] = $image;
            } else {

                // Preserve existing image
                $postData['image'] = $mobileAd->image;
            }

            // Generate slug if empty
            if (empty($postData['slug']) && !empty($postData['ad_title'])) {
                $postData['slug'] = strSlug($postData['ad_title']);
            }

            $mobileAd->fill($postData);

            if ($this->mobileAdModel->trySave($mobileAd)) {

                setSuccessMessage("msg_updated");

                return redirect()->to(getBackUrl());
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->mobileAdModel->errors());
        }

        return view('admin/mobile_ads/form', [
            'title' => 'Edit Mobile Ad',
            'action' => adminUrl('mobile-ads/edit/' . esc($id)),
            'mobileAd' => $mobileAd,
            'breadcrumbLink' => [
                'label' => 'Mobile Ads',
                'url'   => adminUrl('mobile-ads')
            ]
        ]);
    }

    /**
     * Delete Mobile Ad
     */
    public function deleteMobileAd()
    {
        $id = (int) inputPost('id');

        $mobileAd = $this->mobileAdModel->find($id);

        if (empty($mobileAd)) {
            return jsonResponse(false);
        }

        if ($this->mobileAdModel->delete($id)) {

            // Delete image after successful database deletion
            if (!empty($mobileAd->image)) {

                $oldFile = FCPATH . $mobileAd->image;

                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            setSuccessMessage("msg_deleted");

            return jsonResponse(true);
        }

        setErrorMessage("msg_error");

        return jsonResponse(false);
    }
}
