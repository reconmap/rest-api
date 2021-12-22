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
                                private AuditLogService  $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->getJsonBodyDecoded($request);

        $operation = $request->getHeaderLine('Bulk-Operation');
        $this->logger->debug("Target Bulk-Operation: $operation");

        $loggedInUserId = $request->getAttribute('userId');

        $targetIds = [];

        switch ($operation) {
            case 'CREATE':
                $lines = explode("\n", $body->lines);
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (empty($trimmedLine))
                        continue;

                    $columns = str_getcsv($line);
                    $columnCount = count($columns);
                    $targetName = $columns[0];
                    $targetKind = 2 === $columnCount ? $columns[1] : 'hostname';

                    $target = new Target();
                    $target->project_id = intval($body->projectId);
                    $target->name = $targetName;
                    $target->kind = $targetKind;
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
