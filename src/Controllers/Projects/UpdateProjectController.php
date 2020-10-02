<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class UpdateProjectController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $column = array_keys($requestBody)[0];
        $value = array_values($requestBody)[0];

        $success = false;
        if (in_array($column, ['client_id'])) {
            $repository = new ProjectRepository($this->db);
            $success = $repository->updateById($projectId, $column, $value);
        }

        return ['success' => $success];
    }
}
