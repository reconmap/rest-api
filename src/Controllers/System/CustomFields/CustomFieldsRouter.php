<?php declare(strict_types=1);

namespace Reconmap\Controllers\System\CustomFields;

use League\Route\RouteCollectionInterface;

class CustomFieldsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->get('/system/custom-fields', GetCustomFieldsController::class);
        $router->post('/system/custom-fields', CreateCustomFieldController::class);
        $router->delete('/system/custom-fields/{fieldId:number}', DeleteCustomFieldController::class);
    }
}
