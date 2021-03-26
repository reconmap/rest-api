<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\Importers\CommandsImporter;
use Reconmap\Repositories\Importers\DocumentsImporter;
use Reconmap\Repositories\Importers\ProjectsImporter;
use Reconmap\Services\AuditLogService;

class ImportDataController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $files = $request->getUploadedFiles();
        $importFile = $files['importFile'];
        $importJsonString = $importFile->getStream()->getContents();

        $userId = $request->getAttribute('userId');

        $response = [];

        $importables = [
            'projects' => ProjectsImporter::class,
            'commands' => CommandsImporter::class,
            'documents' => DocumentsImporter::class
        ];

        $json = json_decode($importJsonString);
        foreach ($json as $entityType => $entities) {
            if (isset($importables[$entityType])) {
                $importer = $this->container->get($importables[$entityType]);
                $response[] = array_merge(['name' => $entityType], $importer->import($userId, $entities));
            } else {
                $this->logger->warning("Trying to import invalid entity type: $entityType");
            }
        }

        $this->auditAction($userId, array_column($response, 'name'));

        return $response;
    }

    private function auditAction(int $loggedInUserId, array $entities): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::DATA_IMPORTED, $entities);
    }
}
