<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityStatsRepository;

class GetVulnerabilitiesStatsController extends Controller
{
    public function __construct(private VulnerabilityStatsRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $projectId = isset($params['projectId']) ? intval($params['projectId']) : null;

        if (!isset($params['groupBy']) || $params['groupBy'] === 'risk') {
            $stats = $this->repository->findCountByRisk($projectId);
        } else {
            $stats = $this->repository->findCountByCategory($projectId);
        }

        return $stats;
    }
}
