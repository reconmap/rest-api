<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\ReportRepository;

#[OpenApi\Get(
    path: "/reports",
    description: "Returns all reports",
    security: ["bearerAuth"],
    tags: ["Reports"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
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
