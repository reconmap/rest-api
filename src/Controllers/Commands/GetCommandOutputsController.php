<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandOutputRepository;

class GetCommandOutputsController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getQueryParams();
        $taskId = (int)$params['taskId'];

        $repository = new CommandOutputRepository($this->db);
        return $repository->findByTaskId($taskId);
    }
}
