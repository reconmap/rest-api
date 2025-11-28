<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use League\Route\RouteCollectionInterface;

class DocumentsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/documents', CreateDocumentController::class);
        $router->map('GET', '/documents', GetDocumentsController::class);
        $router->map('GET', '/documents/{documentId:number}', GetDocumentController::class);
        $router->map('PUT', '/documents/{documentId:number}', UpdateDocumentController::class);
    }
}
