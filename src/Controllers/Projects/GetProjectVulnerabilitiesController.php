<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VulnerabilityRepository;

class GetProjectVulnerabilitiesController extends Controller
{
    public function __construct(private VulnerabilityRepository $vulnerabilityRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        return $this->vulnerabilityRepository->findByProjectId($projectId);
    }
}
