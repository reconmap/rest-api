<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\Importers\Importable;
use Reconmap\Repositories\Importers\Importables;
use Reconmap\Services\AuditLogService;

class ImportDataController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
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

        foreach ($json as $entityType => $entities) {
            if (isset(Importables::List[$entityType])) {
                /** @var Importable $importer */
                $importer = $this->container->get(Importables::List[$entityType]);
                $response['results'][] = array_merge(['name' => $entityType], $importer->import($userId, $entities));
            } else {
                $this->logger->warning("Trying to import invalid entity type: $entityType");
                $response['errors'][] = "Invalid entity '$entityType' found in file. Expected one of: " . implode(', ', array_keys(Importables::List));
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
