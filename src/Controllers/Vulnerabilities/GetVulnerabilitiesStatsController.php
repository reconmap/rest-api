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
		$repository = new VulnerabilityRepository($this->db);
		$stats = $repository->findCountByRisk();

		return $stats;
	}
}
