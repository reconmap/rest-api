<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\DocumentAuditActions;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateDocumentController extends Controller
{
    public function __construct(
        private DocumentRepository       $repository,
        private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $documentId = (int)$args['documentId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(DocumentRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            $success = $this->repository->updateById($documentId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $documentId);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $commandId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, DocumentAuditActions::MODIFIED, ['type' => 'document', 'id' => $commandId]);
    }
}
