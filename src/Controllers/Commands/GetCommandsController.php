<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class GetCommandsController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __construct(private CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        if (isset($params['keywords'])) {
            return $this->repository->findByKeywords($params['keywords'], $limit);
        } else {
            return $this->repository->findAll($limit);
        }
    }
}
