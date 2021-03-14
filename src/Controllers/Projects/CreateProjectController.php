<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;

class CreateProjectController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        /** @var Project $project */
        $project = $this->getJsonBodyDecoded($request);
        $project->creator_uid = $request->getAttribute('userId');
        $project->isTemplate = false;

        $result = $this->projectRepository->insert($project);

        return ['success' => $result];
    }
}
