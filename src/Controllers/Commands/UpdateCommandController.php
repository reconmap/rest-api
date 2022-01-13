<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateCommandController extends Controller
{
    public function __construct(private CommandRepository        $repository,
                                private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = (int)$args['commandId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(CommandRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            $success = $this->repository->updateById($commandId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $commandId);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $commandId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::COMMAND_UPDATED, ['type' => 'command', 'id' => $commandId]);
    }
}
