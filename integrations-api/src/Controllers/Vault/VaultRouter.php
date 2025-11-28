<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use League\Route\RouteCollectionInterface;

class VaultRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/vault', GetVaultSecretsController::class);
        $router->map('POST', '/vault', CreateVaultItemController::class);
        $router->map('DELETE', '/vault/{vaultItemId:number}', DeleteVaultItemController::class);
        $router->map('POST', '/vault/{vaultItemId:number}', ReadVaultItemController::class);
        $router->map('PUT', '/vault/{vaultItemId:number}', UpdateVaultItemController::class);
    }
}
