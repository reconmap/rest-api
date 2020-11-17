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
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(ProjectRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            $repository = new ProjectRepository($this->db);
            $success = $repository->updateById($projectId, $newColumnValues);
        }

        return ['success' => $success];
    }
}
