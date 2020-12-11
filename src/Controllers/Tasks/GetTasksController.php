<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTasksController extends Controller
{
    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $repository = new TaskRepository($this->db);
        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $tasks = $repository->findByKeywords($keywords);
        } else {
            $tasks = $repository->findAll();
        }

        return $tasks;
    }
}
