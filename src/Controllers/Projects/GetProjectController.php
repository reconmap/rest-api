<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;
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
            $project = $this->projectRepository->findById($projectId);
            if (is_null($project)) {
                throw new NotFoundException("Project #$projectId not found");
            }
            return $project;
        }

        throw new ForbiddenException("Project #$projectId not visible to user #{$user->id}");
    }
}
