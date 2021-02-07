<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Services\TemplateEngine;

class GetNotesController extends Controller
{
    private NoteRepository $repository;

    public function __construct(Logger $logger, \mysqli $db, TemplateEngine $template, NoteRepository $repository)
    {
        parent::__construct($logger, $db, $template);

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
