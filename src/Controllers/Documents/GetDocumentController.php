<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\DocumentRepository;

class GetDocumentController extends Controller
{
    public function __construct(private DocumentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $documentId = (int)$args['documentId'];

        return $this->repository->findById($documentId);
    }
}
