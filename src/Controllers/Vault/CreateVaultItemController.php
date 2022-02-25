<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class CreateVaultItemController extends Controller
{
    public function __construct(private VaultRepository $repository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $vault = $this->getJsonBodyDecodedAsClass($request, new Vault());
        $vault->project_id = (int)$args['projectId'];
        
        $this->repository->insert($vault);
        $userId = $request->getAttribute('userId');

        $this->auditAction($userId, $vault->name);

        return $this->createStatusCreatedResponse($vault);
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, VaultAuditActions::ITEM_CREATED, [$name]);
    }
}
