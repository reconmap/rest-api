<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use League\Route\RouteCollectionInterface;

class ClientsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/clients', CreateClientController::class);
        $router->map('PUT', '/clients/{clientId:number}', UpdateClientController::class);
    }
}
