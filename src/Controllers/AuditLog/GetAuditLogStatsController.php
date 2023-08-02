<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use OpenApi\Attributes as OpenApi;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

#[OpenApi\Get(path: "/auditlog/stats", description: "Get audit log statistics", security: ["bearerAuth"], tags: ["Audit log"])]
#[OpenApi\Response(response: 200, description: "Ok response")]
#[OpenApi\Response(response: 403, description: "Authorization error")]
class GetAuditLogStatsController extends Controller
{
    public function __construct(private AuditLogRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->repository->findCountByDayStats();
    }
}
