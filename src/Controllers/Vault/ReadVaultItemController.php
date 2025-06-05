<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class ReadVaultItemController extends Controller
{
    public function __construct(private readonly VaultRepository $repository,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $requestArray = $this->getJsonBodyDecodedAsArray($request);
        $password = $requestArray['password'];
        $projectId = (int)$args['projectId'];
        $vaultItemId = (int)$args['vaultItemId'];

        $vault = $this->repository->readVaultItem($projectId, $vaultItemId, $password);
        if ($vault) {
            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $vault->name);

            return $this->createStatusCreatedResponse($vault);
        }

        return $this->createBadRequestResponse();
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::READ, 'Vault Item', [$name]);
    }
}
