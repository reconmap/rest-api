<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\AttachmentRepository;

#[OpenApi\Get(path: "/attachments", description: "Returns all attachments", security: ["bearerAuth"], tags: ["Attachments"])]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
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
