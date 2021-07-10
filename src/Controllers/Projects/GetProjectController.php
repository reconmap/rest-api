<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class GetProjectController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $user = $this->getUserFromRequest($request);

        if ($user->isAdministrator() || $this->projectRepository->isVisibleToUser($projectId, $user->id)) {
            return $this->projectRepository->findById($projectId);
        }

        throw new ForbiddenException('Project not visible to user');
    }
}
