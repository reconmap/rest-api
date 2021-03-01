<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTasksController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __construct(private TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $tasks = $this->repository->findByKeywords($keywords);
        } else {
            $tasks = $this->repository->findAll(limit: $limit);
        }

        return $tasks;
    }
}
