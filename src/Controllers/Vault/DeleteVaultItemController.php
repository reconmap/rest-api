<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Reconmap\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

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
        
        // TODO: check if vault item is in the correct project
        //  Possibly also audit log the name of vaultId
        $success = $this->repository->deleteById($vaultId);
        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $projectId, $vaultId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $projectId, int $vaultId): void
    {
        $this->auditLogService->insert($loggedInUserId, VaultAuditActions::ITEM_DELETED, [$projectId, $vaultId]);
    }
}
