<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Users\CreateUserController;
use Reconmap\Controllers\Users\DeleteUserController;
use Reconmap\Controllers\Users\GetUserController;
use Reconmap\Controllers\Users\GetUserActivityController;
use Reconmap\Controllers\Users\GetUsersController;
use Reconmap\Controllers\Users\UpdateUserController;
use Reconmap\Controllers\Users\UsersLogoutController;

class UsersRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/logout', UsersLogoutController::class);
        $router->map('GET', '/users', GetUsersController::class);
        $router->map('POST', '/users', CreateUserController::class);
        $router->map('GET', '/users/{id:number}', GetUserController::class);
        $router->map('PATCH', '/users/{id:number}', UpdateUserController::class);
        $router->map('GET', '/users/{id:number}/activity', GetUserActivityController::class);
        $router->map('DELETE', '/users/{id:number}', DeleteUserController::class);
    }
}
