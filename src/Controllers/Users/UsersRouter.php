<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use League\Route\RouteCollectionInterface;

class UsersRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/logout', UsersLogoutController::class);
        $router->map('GET', '/users', GetUsersController::class);
        $router->map('POST', '/users', CreateUserController::class);
        $router->map('PATCH', '/users', BulkUpdateUsersController::class);
        $router->map('GET', '/users/{userId:number}', GetUserController::class);
        $router->map('PATCH', '/users/{userId:number}', UpdateUserController::class);
        $router->map('GET', '/users/{userId:number}/activity', GetUserActivityController::class);
        $router->map('PATCH', '/users/{userId:number}/password', UpdateUserPasswordController::class);
        $router->map('DELETE', '/users/{userId:number}', DeleteUserController::class);
    }
}
