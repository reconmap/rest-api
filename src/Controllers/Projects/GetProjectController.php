<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class GetProjectController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $repository = new ProjectRepository($this->db);
        return $repository->findById($projectId);
    }
}
