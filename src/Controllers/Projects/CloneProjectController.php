<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class CloneProjectController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectTemplateId = (int)$args['projectId'];
        $userId = $request->getAttribute('userId');

        return $this->projectRepository->clone($projectTemplateId, $userId);
    }
}
