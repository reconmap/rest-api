<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\AuditLogRepository;

#[OpenApi\Get(path: "/auditlog/stats", description: "Returns audit log statistics", security: ["bearerAuth"], tags: ["Audit log"])]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetAuditLogStatsController extends Controller
{
    public function __construct(private readonly AuditLogRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->repository->findCountByDayStats();
    }
}
