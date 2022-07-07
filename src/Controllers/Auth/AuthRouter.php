<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use League\Route\RouteCollectionInterface;

class AuthRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/login', LoginController::class);
        $router->map('POST', '/users/logout', LogoutController::class);
    }
}
