<?php declare(strict_types=1);

namespace Reconmap\Controllers\Contacts;

use League\Route\RouteCollectionInterface;

class ContactsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/clients/{clientId:number}/contacts', CreateContactController::class);
        $router->map('DELETE', '/contacts/{contactId:number}', DeleteContactController::class);
    }
}
