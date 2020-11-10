<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectUserRepository;

class DeleteProjectUserController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $membershipId = (int)$args['membershipId'];

        $repository = new ProjectUserRepository($this->db);
        $result = $repository->deleteById($membershipId);

        return ['success' => $result];
    }
}
