<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use League\Route\RouteCollectionInterface;

class ClientsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/clients', CreateClientController::class);
        $router->map('GET', '/clients', GetClientsController::class);
        $router->map('GET', '/clients/{id:number}', GetClientController::class);
        $router->map('DELETE', '/clients/{id:number}', DeleteClientController::class);
    }
}
