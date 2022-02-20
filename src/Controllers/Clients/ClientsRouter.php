<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use League\Route\RouteCollectionInterface;

class ClientsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/clients', CreateClientController::class);
        $router->map('GET', '/clients', GetClientsController::class);
        $router->map('GET', '/clients/{clientId:number}', GetClientController::class);
        $router->map('GET', '/clients/{clientId:number}/contacts', GetClientContactsController::class);
        $router->map('PUT', '/clients/{clientId:number}', UpdateClientController::class);
        $router->map('DELETE', '/clients/{clientId:number}', DeleteClientController::class);
    }
}
