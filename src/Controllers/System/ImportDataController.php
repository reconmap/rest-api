<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\Importers\CommandsImporter;
use Reconmap\Repositories\Importers\DocumentsImporter;
use Reconmap\Repositories\Importers\Importable;
use Reconmap\Repositories\Importers\ProjectsImporter;
use Reconmap\Repositories\Importers\TargetsImporter;
use Reconmap\Repositories\Importers\VulnerabilitiesImporter;
use Reconmap\Repositories\Importers\VulnerabilityTemplatesImporter;
use Reconmap\Services\AuditLogService;

class ImportDataController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $response = [
            'errors' => [],
            'results' => []
        ];

        $files = $request->getUploadedFiles();
        /** @var UploadedFileInterface $importFile */
        $importFile = $files['importFile'];

        if ($importFile->getSize() === 0) {
            $response['errors'][] = 'Uploaded file is empty.';
            return $response;
        }

        $importJsonString = $importFile->getStream()->getContents();
        $json = json_decode($importJsonString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Invalid JSON file: ' . json_last_error_msg(), [$importFile->getClientFilename()]);
            $response['errors'][] = 'Invalid JSON file.';
            return $response;
        }

        $userId = $request->getAttribute('userId');

        $importables = [
            'projects' => ProjectsImporter::class,
            'project_templates' => ProjectsImporter::class,
            'commands' => CommandsImporter::class,
            'documents' => DocumentsImporter::class,
            'targets' => TargetsImporter::class,
            'vulnerabilities' => VulnerabilitiesImporter::class,
            'vulnerability_templates' => VulnerabilityTemplatesImporter::class,
        ];

        foreach ($json as $entityType => $entities) {
            if (isset($importables[$entityType])) {
                /** @var Importable $importer */
                $importer = $this->container->get($importables[$entityType]);
                $response['results'][] = array_merge(['name' => $entityType], $importer->import($userId, $entities));
            } else {
                $this->logger->warning("Trying to import invalid entity type: $entityType");
                $response['errors'][] = "Invalid entity '$entityType' found in file. Expected one of: " . implode(', ', array_keys($importables));
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
