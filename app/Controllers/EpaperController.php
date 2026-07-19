<?php

namespace App\Controllers;

use App\Entities\Epaper;

class EpaperController extends BaseAdminController
{
    protected $epaperModel;
    protected $publicationModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        checkPermission('epaper');

        $this->epaperModel = model('EpaperModel');
        $this->publicationModel = model('PublicationModel');
    }

    /**
     * List issues for a publication
     */
    public function issues($publicationId)
    {
        $publication = $this->publicationModel->find($publicationId);

        if (empty($publication)) {
            return redirect()->to(adminUrl('publications'));
        }

        $filters = [
            'publication_id' => $publicationId,
            'status' => inputGet('status'),
            'source_type' => inputGet('source_type'),
            'q' => cleanStr(inputGet('q'))
        ];

        return view('admin/e-paper/papers', [
            'title' => $publication->title,
            'publication' => $publication,
            'panelSettings' => panelSettings(),
            'issues' => $this->epaperModel->findAllPaginated($filters, $this->perPage),
            'pager' => $this->epaperModel->pager
        ]);
    }

    /**
     * Add issue
     */
    public function addIssue($publicationId)
    {
        checkPermission('add_epaper');

        $publication = $this->publicationModel->find($publicationId);

        if (empty($publication)) {
            return redirect()->to(adminUrl('publications'));
        }

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            $postData['publication_id'] = $publicationId;
            $postData['user_id'] = user()->id;

            // Upload cover image
            $coverImage = $this->uploadFile('cover_image', 'epapers/covers');
            if ($coverImage !== null) {
                $postData['cover_image'] = $coverImage;
            }

            // Upload PDF
            $pdfFile = $this->uploadFile('pdf_file', 'epapers/pdfs');
            if ($pdfFile !== null) {
                $postData['pdf_file'] = $pdfFile;
            }

            $issue = new Epaper();
            $issue->fill($postData);

            if ($this->epaperModel->trySave($issue)) {

                setSuccessMessage("msg_added");

                return redirect()->to(adminUrl("publications/$publicationId/issues"));
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->epaperModel->errors());
        }
        $categorySelectorData = model('CategoryModel')
            ->selectorGetTree(
                $publication->category_id,
                $publication->lang_id
            );
        return view('admin/e-paper/form', [
            'title' => 'Add Issue',
            'publication' => $publication,
            'categorySelectorData' => json_encode($categorySelectorData),

            'action' => adminUrl("publications/$publicationId/issues/add"),
            'breadcrumbLink' => [
                'label' => $publication->title,
                'url' => adminUrl("publications/$publicationId/issues")
            ]
        ]);
    }

    /**
     * Edit issue
     */
    public function editIssue($id)
    {
        checkPermission('edit_epaper');

        $issue = $this->epaperModel->find($id);

        if (empty($issue)) {
            return redirect()->to(adminUrl('publications'));
        }

        $publication = $this->publicationModel->find($issue->publication_id);

        if (isPostMethod()) {

            $postData = $this->request->getPost();


            //

            $coverImage = $this->uploadFile('cover_image', 'epapers/covers');

            if ($coverImage !== null) {

                if (!empty($issue->cover_image)) {

                    $old = FCPATH . $issue->cover_image;

                    if (is_file($old)) {
                        unlink($old);
                    }
                }

                $postData['cover_image'] = $coverImage;
            } else {
                $postData['cover_image'] = $issue->cover_image;
            }

            //

            $pdfFile = $this->uploadFile('pdf_file', 'epapers/pdfs');

            if ($pdfFile !== null) {

                if (!empty($issue->pdf_file)) {

                    $old = FCPATH . $issue->pdf_file;

                    if (is_file($old)) {
                        unlink($old);
                    }
                }

                $postData['pdf_file'] = $pdfFile;
            } else {
                $postData['pdf_file'] = $issue->pdf_file;
            }


            $issue->fill($postData);

            if ($this->epaperModel->trySave($issue)) {

                setSuccessMessage("msg_updated");

                return redirect()->to(
                    adminUrl("publications/{$issue->publication_id}/issues")
                );
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->epaperModel->errors());
        }

        return view('admin/e-paper/form', [
            'title' => 'Edit Issue',
            'issue' => $issue,
            'publication' => $publication,
            'action' => adminUrl("issues/edit/$id"),
            'breadcrumbLink' => [
                'label' => $publication->title,
                'url' => adminUrl("publications/{$publication->id}/issues")
            ]
        ]);
    }

    /**
     * Delete issue
     */
    public function deleteIssue()
    {
        $id = (int) inputPost('id');

        $issue = $this->epaperModel->find($id);

        if (empty($issue)) {
            return jsonResponse(false);
        }

        if ($this->epaperModel->delete($id)) {

            setSuccessMessage("msg_deleted");

            return jsonResponse(true);
        }

        setErrorMessage("msg_error");

        return jsonResponse(false);
    }
}
