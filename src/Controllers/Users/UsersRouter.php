<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use League\Route\RouteCollectionInterface;

class UsersRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/logout', UsersLogoutController::class);
        $router->map('GET', '/users', GetUsersController::class);
        $router->map('POST', '/users', CreateUserController::class);
        $router->map('PATCH', '/users', UpdateUsersController::class);
        $router->map('GET', '/users/{id:number}', GetUserController::class);
        $router->map('PATCH', '/users/{id:number}', UpdateUserController::class);
        $router->map('GET', '/users/{id:number}/activity', GetUserActivityController::class);
        $router->map('DELETE', '/users/{id:number}', DeleteUserController::class);
    }
}
