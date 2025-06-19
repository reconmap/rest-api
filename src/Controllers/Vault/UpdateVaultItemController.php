<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class UpdateVaultItemController extends Controller
{
    public function __construct(private readonly VaultRepository $repository,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $requestArray = $this->getJsonBodyDecodedAsArray($request);
        $password = $requestArray['password'];
        $id = (int)$args['vaultItemId'];

        $newColumnValues = array_filter(
            $requestArray,
            fn(string $key) => in_array($key, array_keys(VaultRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            $success = $this->repository->updateVaultItemById($id, $password, $newColumnValues);

            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $newColumnValues['name']);
        }

        return $this->createStatusCreatedResponse(['success' => $success]);
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::UPDATED, 'Vault Item', [$name]);
    }
}
