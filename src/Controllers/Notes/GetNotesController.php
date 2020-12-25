<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;

class GetNotesController extends Controller
{
    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $projectId = (int)$params['projectId'];

        $repository = new NoteRepository($this->db);
        return $repository->findByParentId('project', $projectId);
    }
}
