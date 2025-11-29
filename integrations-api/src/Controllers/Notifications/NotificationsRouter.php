<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use League\Route\RouteCollectionInterface;

class NotificationsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/notifications', GetNotificationsController::class);
        $router->map('PATCH', '/notifications', BulkUpdateNotificationsController::class);
        $router->map('PUT', '/notifications/{notificationId:number}', UpdateNotificationController::class);
    }
}
