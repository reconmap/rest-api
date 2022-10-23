<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\VaultAuditActions;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class UpdateVaultItemController extends Controller
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
        $id = (int)$args['vaultItemId'];

        $new_column_values = array_filter(
            $request_array,
            fn(string $key) => in_array($key, array_keys(VaultRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($new_column_values)) {
            $success = $this->repository->updateVaultItemById($id, $project_id, $password, $new_column_values);

            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $new_column_values['name']);
        }

        return $this->createStatusCreatedResponse(['success' => $success]);
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, VaultAuditActions::ITEM_UPDATED, [$name]);
    }
}
