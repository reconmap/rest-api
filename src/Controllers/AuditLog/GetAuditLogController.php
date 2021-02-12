<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogController extends Controller
{
    private AuditLogRepository $repository;

    public function __construct(AuditLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = (int)$params['page'];

        $auditLog = $this->repository->findAll($page);
        $count = $this->repository->countAll();

        $pageCount = max(ceil($count / 20), 1);

        $response = new Response;
        $response->getBody()->write(json_encode($auditLog));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
