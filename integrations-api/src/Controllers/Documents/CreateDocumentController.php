<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default201CreatedResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Models\Document;
use Reconmap\Repositories\DocumentRepository;

#[OpenApi\Post(
    path: "/documents",
    description: "Creates a new document",
    security: ["bearerAuth"],
    tags: ["Documents"],
)]
#[Default201CreatedResponse]
#[Default403UnauthorisedResponse]
class CreateDocumentController extends Controller
{
    public function __construct(private readonly DocumentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var Document $document */
        $document = $this->getJsonBodyDecodedAsClass($request, new Document());
        $document->user_id = $request->getAttribute('userId');

        $result = $this->repository->insert($document);

        return ['success' => $result];
    }
}
