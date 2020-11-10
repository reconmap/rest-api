<?php

declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogStatsController extends Controller
{

    public function __invoke(ServerRequestInterface $request): array
    {
        $repository = new AuditLogRepository($this->db);
        $auditLog = $repository->findCountByDayStats();

        return $auditLog;
    }
}
