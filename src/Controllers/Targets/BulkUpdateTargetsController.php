<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateTargetsController extends Controller
{
    public function __construct(private TargetRepository $repository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->getJsonBodyDecoded($request);

        $operation = $request->getHeaderLine('Bulk-Operation');
        $this->logger->debug("Bulk-Operation: $operation");

        $loggedInUserId = $request->getAttribute('userId');

        $targetIds = [];

        switch ($operation) {
            case 'CREATE':
                $lines = explode("\n", $body->lines);
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;

                    list($name, $kind) = str_getcsv($line);
                    $this->logger->debug("Bulk-Operation: $name - $kind", [$line]);

                    $target = new Target();
                    $target->projectId = intval($body->projectId);
                    $target->name = $name;
                    $target->kind = $kind;
                    try {
                        $targetIds[] = $this->repository->insert($target);
                    } catch (\Exception $e) {
                        $this->logger->warning($e->getMessage());
                    }
                }

                $this->auditLogService->insert($loggedInUserId, AuditLogAction::TARGET_CREATED, ['type' => 'targets', 'ids' => $targetIds]);
                break;
        }

        return $this->createStatusCreatedResponse($targetIds);
    }
}
