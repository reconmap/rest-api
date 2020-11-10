<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetProjectUsersController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['id'];

        $repository = new UserRepository($this->db);
        $targets = $repository->findByProjectId($id);

        return $targets;
    }
}
