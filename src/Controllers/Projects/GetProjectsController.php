<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class GetProjectsController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        if (isset($params['isTemplate'])) {
            $projects = $this->projectRepository->findTemplateProjects((int)$params['isTemplate']);
        } else {
            $projects = $this->projectRepository->findTemplateProjects(0);
        }

        return $projects;
    }
}
