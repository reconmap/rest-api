<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class GetTargetsController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getQueryParams();
        $projectId = (int)$params['projectId'];

        $repository = new TargetRepository($this->db);
        return $repository->findByProjectId($projectId);
    }
}
