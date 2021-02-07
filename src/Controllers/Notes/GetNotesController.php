<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;

class GetNotesController extends Controller
{
    private NoteRepository $repository;

    public function __construct(NoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        return $this->repository->findByParentId($parentType, $parentId);
    }
}
