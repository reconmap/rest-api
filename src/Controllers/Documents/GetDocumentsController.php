<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\DocumentRepository;

class GetDocumentsController extends Controller
{
    public function __construct(private readonly DocumentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'] ?? 'library';
        $parentId = isset($params['parentId']) ? intval($params['parentId']) : null;

        return $this->repository->findByParentId($parentType, $parentId);
    }
}
