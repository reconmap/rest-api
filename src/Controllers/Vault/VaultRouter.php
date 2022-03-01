<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use League\Route\RouteCollectionInterface;

class VaultRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/vault/{projectId:number}', CreateVaultItemController::class);
        $router->map('DELETE', '/vault/{projectId:number}/{vaultItemId:number}', DeleteVaultItemController::class);
        $router->map('GET', '/vault/{projectId:number}', ReadProjectVaultController::class);
    }
}
