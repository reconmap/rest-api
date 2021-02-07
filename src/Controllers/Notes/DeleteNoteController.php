<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;

class DeleteNoteController extends Controller
{
    private NoteRepository $repository;

    public function __construct(NoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $noteId = (int)$args['noteId'];

        $success = $this->repository->deleteById($noteId);

        return ['success' => $success];
    }
}
