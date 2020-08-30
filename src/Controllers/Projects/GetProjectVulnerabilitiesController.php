<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityRepository;

class GetProjectVulnerabilitiesController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = (int)$args['id'];

		$repository = new VulnerabilityRepository($this->db);
		$vulnerabilities = $repository->findByProjectId($id);

		return $vulnerabilities;
	}
}
