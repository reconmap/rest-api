<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\Vault;
use Reconmap\Repositories\VaultRepository;
use Reconmap\Services\AuditLogService;

class CreateVaultItemController extends Controller
{
    public function __construct(private readonly VaultRepository $repository,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $requestArray = $this->getJsonBodyDecodedAsArray($request);
        $password = $requestArray['password'];
        unset($requestArray['password']);

        $jsonRequest = json_decode(json_encode($requestArray));
        $jsonMapper = new \JsonMapper();
        $vault = $jsonMapper->map($jsonRequest, new Vault());

        $userId = $request->getAttribute('userId');
        $vault->owner_uid = $userId;

        $this->repository->insert($vault, $password);

        $this->auditAction($userId, $vault->name);

        return $this->createStatusCreatedResponse($vault);
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::CREATED, 'Vault Secret', [$name]);
    }
}
