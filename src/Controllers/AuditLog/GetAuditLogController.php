<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogController extends Controller
{
    private const PAGE_LIMIT = 20;

    private AuditLogRepository $repository;

    public function __construct(AuditLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = (int)$params['page'];
        $limit = isset($params['limit']) ? intval($params['limit']) : self::PAGE_LIMIT;

        $auditLog = $this->repository->findAll($page, $limit);
        $count = $this->repository->countAll();

        $pageCount = max(ceil($count / self::PAGE_LIMIT), 1);

        $response = new Response;
        $response->getBody()->write(json_encode($auditLog));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
