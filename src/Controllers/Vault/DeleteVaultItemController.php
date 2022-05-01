<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class DeleteVaultItemController extends Controller
{
    public function __construct(private VaultRepository $repository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];
        $vaultId = (int)$args['vaultItemId'];

        $name = $this->repository->getVaultItemName($vaultId, $projectId);
        $success = $this->repository->deleteByIdAndProjectId($vaultId, $projectId);
        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $projectId, $vaultId, $name);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $projectId, int $vaultId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, VaultAuditActions::ITEM_DELETED, [$projectId, $vaultId, $name]);
    }
}
