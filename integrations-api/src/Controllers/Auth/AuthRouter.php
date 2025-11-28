<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use League\Route\RouteCollectionInterface;

class AuthRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/sessions', LoginController::class);
        $router->map('DELETE', '/sessions', LogoutController::class);
    }
}
