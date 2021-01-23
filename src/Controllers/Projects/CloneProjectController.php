<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class CloneProjectController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectTemplateId = (int)$args['id'];
        $userId = $request->getAttribute('userId');

        $repository = new ProjectRepository($this->db);
        return $repository->createFromTemplate($projectTemplateId, $userId);
    }
}
