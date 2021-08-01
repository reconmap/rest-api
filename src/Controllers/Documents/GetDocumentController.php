<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use League\Route\Http\Exception\NotFoundException;
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

        $document = $this->repository->findById($documentId);
        if (is_null($document)) {
            throw new NotFoundException("Document #$documentId not found");
        }

        return $document;
    }
}
