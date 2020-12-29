<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use League\Route\RouteCollectionInterface;

class AuditLogRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/auditlog', GetAuditLogController::class);
        $router->map('GET', '/auditlog/export', ExportAuditLogController::class);
        $router->map('GET', '/auditlog/stats', GetAuditLogStatsController::class);
    }
}
