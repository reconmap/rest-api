<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use League\Route\Http\Exception\NotFoundException;
use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\DocumentRepository;

#[OpenApi\Get(
    path: "/documents/{documentId}",
    description: "Returns information about the document with the given id",
    security: ["bearerAuth"],
    tags: ["Documents"],
    parameters: [new InPathIdParameter("documentId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
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
