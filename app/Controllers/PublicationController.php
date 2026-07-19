<?php

namespace App\Controllers;

class PublicationController extends BaseAdminController
{
    protected $publicationModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        checkPermission('epapers');

        $this->publicationModel = model('PublicationModel');
    }

    /**
     * Publications
     */
    public function publications()
    {
        $filters = [
            'lang_id'          => (int) inputGet('lang_id'),
            'publication_type' => cleanStr(inputGet('publication_type')),
            'status'           => inputGet('status'),
            'q'                => cleanStr(inputGet('q')),
        ];

        return view('admin/publications/publications', [
            'title'         => 'Publications',
            'panelSettings' => panelSettings(),
            'publications'  => $this->publicationModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->publicationModel->pager,
        ]);
    }

    /**
     * Add Publication
     */
    public function addPublication()
    {
        checkPermission('add_epaper');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $postData['user_id'] = user()->id;

            // Generate slug if empty
            if (empty($postData['slug']) && !empty($postData['title'])) {
                $postData['slug'] = strSlug($postData['title']);
            }
            // $postData = $this->request->getPost();
            $logo = $this->uploadFile('logo', 'newspapers');
            // $logo = $this->uploadLogo();

            if ($logo !== null) {
                $postData['logo'] = $logo;
            }
            $publication = new \App\Entities\Publication();
            $publication->fill($postData);

            if ($this->publicationModel->trySave($publication)) {
                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            }

            return redirect()->to(getBackUrl())
                ->withInput()
                ->with('errors', $this->publicationModel->errors());
        }

        return view('admin/publications/form', [
            'title'          => 'Add Publication',
            'action'         => adminUrl('publications/add'),
            'breadcrumbLink' => [
                'label' => 'Publications',
                'url'   => adminUrl('publications')
            ]
        ]);
    }

    /**
     * Edit Publication
     */
    public function editPublication($id)
    {
        checkPermission('edit_epaper');

        $publication = $this->publicationModel->find($id);

        if (empty($publication)) {
            return redirect()->to(adminUrl('publications'));
        }

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            // Upload new logo if selected
            $logo = $this->uploadFile('logo', 'newspapers');

            if ($logo !== null) {

                // Delete old logo
                if (!empty($publication->logo)) {

                    $oldFile = FCPATH . $publication->logo;

                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $postData['logo'] = $logo;
            } else {
                // Preserve existing logo
                $postData['logo'] = $publication->logo;
            }

            $publication->fill($postData);

            if ($this->publicationModel->trySave($publication)) {

                setSuccessMessage("msg_updated");

                return redirect()->to(getBackUrl());
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->publicationModel->errors());
        }

        // Category selector
        $categorySelectorData = model('CategoryModel')
            ->selectorGetTree(
                $publication->category_id,
                $publication->lang_id
            );

        return view('admin/publications/form', [
            'title' => 'Edit Publication',
            'action' => adminUrl('publications/edit/' . esc($id)),
            'publication' => $publication,
            'categorySelectorData' => json_encode($categorySelectorData),
            'breadcrumbLink' => [
                'label' => 'Publications',
                'url' => adminUrl('publications')
            ]
        ]);
    }

    /**
     * Delete Publication
     */
    public function deletePublication()
    {
        $id = (int) inputPost('id');

        $publication = $this->publicationModel->find($id);

        if (empty($publication)) {
            return jsonResponse(false);
        }

        if ($this->publicationModel->delete($id)) {
            setSuccessMessage("msg_deleted");
            return jsonResponse(true);
        }

        setErrorMessage("msg_error");

        return jsonResponse(false);
    }

    protected function uploadLogo(): ?string
    {
        $file = $this->request->getFile('logo');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $folder = FCPATH . 'uploads/newspapers/' . date('Ym');

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $newName = $file->getRandomName();

        $file->move($folder, $newName);

        return 'uploads/newspapers/' . date('Ym') . '/' . $newName;
    }
}
