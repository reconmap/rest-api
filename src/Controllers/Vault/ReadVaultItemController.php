<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class ReadVaultItemController extends Controller
{
    public function __construct(private VaultRepository $repository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $request_array = $this->getJsonBodyDecodedAsArray($request);
        $password = $request_array['password'];
        $project_id = (int)$args['projectId'];
        $vault_item_id = (int)$args['vaultItemId'];

        $vault = $this->repository->readVaultItem($project_id, $vault_item_id, $password);
        if ($vault) {
            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $vault->name);
            return $this->createStatusCreatedResponse($vault);
        } else {
            return $this->createStatusCreatedResponse(['success' => false]);
        }
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::READ, 'Vault Item', [$name]);
    }
}
