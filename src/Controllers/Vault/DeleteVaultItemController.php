<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class DeleteVaultItemController extends Controller
{
    public function __construct(private readonly VaultRepository $repository,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $vaultId = (int)$args['vaultItemId'];
        $userId = $request->getAttribute('userId');

        $success = $this->repository->deleteByIdAndUserId($vaultId, $userId);

        if ($success) {
            $this->auditAction($userId, $vaultId);
            return $this->createDeletedResponse();
        }

        return $this->createBadRequestResponse();
    }

    private function auditAction(int $loggedInUserId, int $vaultId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::DELETED, 'Vault Secret', [$vaultId]);
    }
}
