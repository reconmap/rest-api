<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Models\Note;

class CreateNoteController extends Controller
{
    public function __construct(private NoteRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $note = $this->getJsonBodyDecodedAsClass($request, new Note());
        $note->user_id = $request->getAttribute('userId');

        $result = $this->repository->insert($note);

        return ['success' => $result];
    }
}
