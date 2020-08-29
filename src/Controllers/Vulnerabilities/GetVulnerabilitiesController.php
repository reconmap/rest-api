<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityRepository;

class GetVulnerabilitiesController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams();
		$page = (int)$params['page'];

		$repository = new VulnerabilityRepository($this->db);
		if(isset($params['keywords'])) {
			$keywords = $params['keywords'];
			$vulnerabilities = $repository->findByKeywords($keywords);
		} else {
			$vulnerabilities = $repository->findAll($page);
		}
		$count = $repository->countAll();

		$pageCount = ceil($count / 20);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($vulnerabilities));
		return $response
			->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
			->withHeader('X-Page-Count', $pageCount);
	}
}
