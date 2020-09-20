<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityRepository;

class GetVulnerabilitiesStatsController extends Controller
{

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $repository = new VulnerabilityRepository($this->db);

        if (!isset($params['groupBy']) || $params['groupBy'] === 'risk') {
            $stats = $repository->findCountByRisk();
        } else {
            $stats = $repository->findCountByCategory();
        }

        return $stats;
    }
}
