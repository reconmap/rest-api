<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\VulnerabilityLogActions;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateVulnerabilitiesController extends Controller
{
    public function __construct(private VulnerabilityRepository $vulnerabilityRepository,
                                private AuditLogService         $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $operation = $request->getHeaderLine('Bulk-Operation');
        $requestData = $this->getJsonBodyDecodedAsArray($request);

        $this->logger->debug("Bulk-Operation: $operation", $requestData);

        $loggedInUserId = $request->getAttribute('userId');

        $numSuccesses = 0;

        if ($operation == 'DELETE') {
            $vulnerabilityIds = $requestData;
            $numSuccesses = $this->vulnerabilityRepository->deleteByIds($vulnerabilityIds);
            $this->auditLogService->insert($loggedInUserId, VulnerabilityLogActions::VULNERABILITY_DELETED, ['type' => 'vulnerability', 'ids' => $vulnerabilityIds]);
        }

        return ['numSuccesses' => $numSuccesses];
    }
}
