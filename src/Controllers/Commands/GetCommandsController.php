<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class GetCommandsController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        $repository = new CommandRepository($this->db);
        return $repository->findAll($limit);
    }
}
