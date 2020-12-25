<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;

class DeleteNoteController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $noteId = (int)$args['noteId'];

        $repository = new NoteRepository($this->db);
        $success = $repository->deleteById($noteId);

        return ['success' => $success];
    }
}
