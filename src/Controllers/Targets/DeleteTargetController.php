<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

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
