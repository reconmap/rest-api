<?php declare(strict_types=1);

namespace Reconmap\Controllers\Agents;

use League\Route\RouteCollectionInterface;

class AgentsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/agents', GetAgentsController::class);
        $router->map('GET', '/agents/ping', PingAgentController::class);
        $router->map('POST', '/agents/boot', BootAgentController::class);
    }
}
