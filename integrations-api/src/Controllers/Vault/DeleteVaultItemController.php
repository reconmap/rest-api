<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

#[OpenApi\Delete(path: "/vault/{secretId}", description: "Deletes secret with the given id", security: ["bearerAuth"], tags: ["Vault"], parameters: [new InPathIdParameter("secretId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
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
