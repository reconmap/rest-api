<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use League\Route\RouteCollectionInterface;

class UsersRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/{userId:number}/actions', CreateUserActionController::class);
        $router->map('PATCH', '/users', BulkUpdateUsersController::class);
        $router->map('PATCH', '/users/{userId:number}', UpdateUserController::class);
    }
}
