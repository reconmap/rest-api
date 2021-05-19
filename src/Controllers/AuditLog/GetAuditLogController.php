<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Services\RequestPaginator;

class GetAuditLogController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __construct(private AuditLogRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $paginator = new RequestPaginator($request);
        $params = $request->getQueryParams();
        $currentPage = $paginator->getCurrentPage();
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        $auditLog = $this->repository->findAll($currentPage, $limit);
        $count = $this->repository->countAll();
        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($auditLog));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
