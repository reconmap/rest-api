<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

#[OpenApi\Delete(path: "/targets/{assetId}", description: "Deletes asset with the given id", security: ["bearerAuth"], tags: ["Assets"], parameters: [new InPathIdParameter("assetId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteTargetController extends Controller
{
    public function __construct(private readonly TargetRepository $repository,
                                private readonly AuditLogService  $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $targetId = (int)$args['targetId'];

        $success = $this->repository->deleteById($targetId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $targetId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }

    private function auditAction(int $loggedInUserId, int $targetId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::DELETED, 'Target', ['id' => $targetId]);
    }
}
