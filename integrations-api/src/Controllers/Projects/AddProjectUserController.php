<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectUserRepository;

class AddProjectUserController extends Controller
{
    public function __construct(private readonly ProjectUserRepository $projectUserRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];
        $userData = $this->getJsonBodyDecoded($request);

        $result = $this->projectUserRepository->create($projectId, (int)$userData->userId);

        return ['success' => $result];
    }
}
