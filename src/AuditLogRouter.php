<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\AuditLog\GetAuditLogStatsController;
use Reconmap\Controllers\AuditLog\ExportAuditLogController;
use Reconmap\Controllers\AuditLog\GetAuditLogController;

class AuditLogRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/auditlog', GetAuditLogController::class);
        $router->map('GET', '/auditlog/export', ExportAuditLogController::class);
        $router->map('GET', '/auditlog/stats', GetAuditLogStatsController::class);
    }
}
