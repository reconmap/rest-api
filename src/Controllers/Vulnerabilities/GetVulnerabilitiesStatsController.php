<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityRepository;

class GetVulnerabilitiesStatsController extends Controller
{
    public function __construct(private VulnerabilityRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        if (!isset($params['groupBy']) || $params['groupBy'] === 'risk') {
            $stats = $this->repository->findCountByRisk();
        } else {
            $stats = $this->repository->findCountByCategory();
        }

        return $stats;
    }
}
