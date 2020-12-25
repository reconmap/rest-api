<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use League\Route\RouteCollectionInterface;

class NotesRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/notes', CreateNoteController::class);
        $router->map('GET', '/notes', GetNotesController::class);
        $router->map('DELETE', '/notes/{noteId:number}', DeleteNoteController::class);
    }
}
