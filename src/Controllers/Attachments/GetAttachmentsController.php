<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;

class GetAttachmentsController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        $repository = new AttachmentRepository($this->db);
        return $repository->findByParentId($parentType, $parentId);
    }
}
