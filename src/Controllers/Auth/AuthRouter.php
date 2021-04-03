<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use League\Route\RouteCollectionInterface;

class AuthRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/users/logout', LogoutController::class);
        $router->map('GET', '/auth/mfa-setup', MfaSetupController::class);
        $router->map('POST', '/auth/mfa-verification', MfaVerificationController::class);
    }
}
