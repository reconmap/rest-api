<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskResultRepository;

class GetTaskResultsController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['taskId'];

        $repository = new TaskResultRepository($this->db);
        return $repository->findByTaskId($id);
    }
}
