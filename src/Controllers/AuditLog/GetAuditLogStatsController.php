<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

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
