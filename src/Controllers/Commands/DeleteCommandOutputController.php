<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\CommandOutputRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteCommandOutputController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $outputId = (int)$args['outputId'];

        $commandRepository = new CommandOutputRepository($this->db);
        $success = $commandRepository->deleteById($outputId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $outputId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $outputId): void
    {
        $activityPublisherService = $this->container->get(ActivityPublisherService::class);
        $activityPublisherService->publish($loggedInUserId, AuditLogAction::COMMAND_OUTPUT_DELETED, ['type' => 'command-output', 'id' => $outputId]);
    }
}
