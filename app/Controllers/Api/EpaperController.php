<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EpaperMobileModel;

class EpaperController extends ResourceController
{
    protected EpaperMobileModel $epaperModel;

    public function __construct()
    {
        $this->epaperModel = new EpaperMobileModel();
    }

    /**
     * Common request parameters
     */
    protected function getRequestParams(): array
    {
        $district = trim((string) $this->request->getGet('district'));

        $district = match ($district) {
            '', 'null', 'NULL', 'all' => null,
            default => $district,
        };
   $sourceType = trim((string) $this->request->getGet('source_type'));

        $sourceType = match ($sourceType) {
            '', 'null', 'NULL', 'all' => null,
            default => $sourceType,
        };

        return [
            'langId'  => (int)($this->request->getGet('lang_id') ?? 1),
            'page'    => max(1, (int)($this->request->getGet('page') ?? 1)),
            'limit'   => max(1, (int)($this->request->getGet('limit') ?? 20)),
            'district' => $district,
            'source_type'=>$sourceType
        ];
    }


    /**
     * GET /api/epapers/newspapers/websites
     */
    public function newspaperWebsites()
    {
        $params = $this->getRequestParams();

        return $this->response->setJSON(
            $this->epaperModel->getIssues(
                ['newspaper'],
                'website',
                $params['langId'],
                $params['page'],
                $params['limit'],
                null,
                null,
                $params['district']
            )
        );
    }


    /**
     * GET /api/epapers/newspapers
     */
    public function newspapers()
    {
        $params = $this->getRequestParams();

        $source_type = trim((string) $this->request->getGet('source_type'));

        $source_type = match ($source_type) {
            '', 'null', 'NULL', 'all' => null,
            default => $source_type,
        };

        return $this->response->setJSON(
            $this->epaperModel->getIssues(
                ['newspaper'],
                $source_type,
                $params['langId'],
                $params['page'],
                $params['limit'],
                null,
                null,
                $params['district']
            )
        );
    }


    /**
     * GET /api/epapers/newspapers/pdfs
     */
    public function newspaperPdfs()
    {
        $params = $this->getRequestParams();

        return $this->response->setJSON(
            $this->epaperModel->getIssues(
                ['newspaper'],
                'pdf',
                $params['langId'],
                $params['page'],
                $params['limit'],
                null,
                null,
                $params['district']
            )
        );
    }


    /**
     * GET /api/epapers/periodicals
     *
     * Weekly + Fortnightly + Monthly
     */
    public function periodicals()
    {
        $params = $this->getRequestParams();

        $frequency = $this->request->getGet('frequency');

        $source_type = trim((string) $this->request->getGet('source_type'));

        $source_type = match ($source_type) {
            '', 'null', 'NULL', 'all' => null,
            default => $source_type,
        };

        return $this->response->setJSON(
            $this->epaperModel->getIssues(
                [
                    'weekly',
                    'fortnightly',
                    'monthly'
                ],
                $source_type,
                $params['langId'],
                $params['page'],
                $params['limit'],
                $frequency,
                null,
                $params['district']
            )
        );
    }


    /**
     * GET /api/epapers/magazines
     */
    public function magazines()
    {
        $params = $this->getRequestParams();

        $categoryId = (int)$this->request->getGet('category_id');

        return $this->response->setJSON(
            $this->epaperModel->getIssues(
                ['magazine'],
                null,
                $params['langId'],
                $params['page'],
                $params['limit'],
                null,
                $categoryId,
                $params['district']
            )
        );
    }


    /**
     * GET /api/epapers/magazine-categories
     */
    public function magazineCategories()
    {
        $params = $this->getRequestParams();

        return $this->response->setJSON(
            $this->epaperModel->getMagazineCategories(
                $params['langId']
            )
        );
    }


    /**
     * GET /api/epapers/featured
     */
    public function featured()
    {
        $params = $this->getRequestParams();

        return $this->response->setJSON(
            $this->epaperModel->getFeaturedIssues(
                $params['langId'],
                $params['limit'],
                $params['district']
            )
        );
    }


    /**
     * GET /api/publications/{id}/issues
     */
    public function publication($publicationId)
    {
        $params = $this->getRequestParams();

        return $this->response->setJSON(
            $this->epaperModel->getPublicationIssues(
                (int)$publicationId,
                $params['langId'],
                $params['page'],
                $params['limit'],
                $params['district'],
                 $params['source_type']
            )
        );
    }


    /**
     * GET /api/epapers/{id}
     */
    public function issue($id)
    {
        $params = $this->getRequestParams();

        $issue = $this->epaperModel->getIssueById(
            (int)$id,
            $params['langId']
        );

        if (empty($issue)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => false,
                    'message' => 'Issue not found'
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => $issue
        ]);
    }
}