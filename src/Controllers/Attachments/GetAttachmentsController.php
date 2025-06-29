<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;

class GetAttachmentsController extends Controller
{

    public function __construct(private readonly AttachmentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        return $this->repository->findByParentId($parentType, $parentId);
    }
}
