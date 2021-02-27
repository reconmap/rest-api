<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteClientController extends Controller
{
    public function __construct(private ClientRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $clientId = (int)$args['clientId'];

        $success = $this->repository->deleteById($clientId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $clientId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $clientId): void
    {
        $activityPublisherService = $this->container->get(ActivityPublisherService::class);
        $activityPublisherService->publish($loggedInUserId, AuditLogAction::CLIENT_DELETED, ['id' => $clientId]);
    }
}
