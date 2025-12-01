<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use League\Route\RouteCollectionInterface;

class VaultRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('PUT', '/vault/{vaultItemId:number}', UpdateVaultItemController::class);
    }
}
