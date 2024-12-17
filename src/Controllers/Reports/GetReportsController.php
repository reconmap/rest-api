<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class GetReportsController extends Controller
{
    public function __construct(private readonly ReportRepository $reportRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        if (isset($params['projectId'])) {
            $projectId = (int)$params['projectId'];
            return $this->reportRepository->findByProjectId($projectId);
        }

        return $this->reportRepository->findAll();
    }
}
