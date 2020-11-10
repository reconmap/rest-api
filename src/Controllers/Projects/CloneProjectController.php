<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class CloneProjectController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['id'];

        $repository = new ProjectRepository($this->db);
        $project = $repository->createFromTemplate($id);

        return $project;
    }
}
