<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use League\Route\RouteCollectionInterface;

class NotificationsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('PATCH', '/notifications', BulkUpdateNotificationsController::class);
    }
}
