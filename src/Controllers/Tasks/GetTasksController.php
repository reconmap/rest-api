<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTasksController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        $repository = new TaskRepository($this->db);
        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $tasks = $repository->findByKeywords($keywords);
        } else {
            $tasks = $repository->findAll(limit: $limit);
        }

        return $tasks;
    }
}
