<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\DocumentRepository;

class CreateDocumentController extends Controller
{
    public function __construct(private DocumentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $document = $this->getJsonBodyDecoded($request);

        $userId = $request->getAttribute('userId');

        $result = $this->repository->insert($userId, $document);

        return ['success' => $result];
    }
}
