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
        $request_array = $this->getJsonBodyDecodedAsArray($request);
        $password = $request_array['password'];
        unset($request_array['password']);
        $json_request = json_decode(json_encode($request_array));
        $json_mapper = new \JsonMapper();
        $vault = $json_mapper->map($json_request, new Vault());
        $vault->project_id = (int)$args['projectId'];

        $this->repository->insert($vault, $password);
        $userId = $request->getAttribute('userId');

        $this->auditAction($userId, $vault->name);

        return $this->createStatusCreatedResponse($vault);
    }

    private function auditAction(int $loggedInUserId, string $name): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::CREATED, 'Vault Item', [$name]);
    }
}
