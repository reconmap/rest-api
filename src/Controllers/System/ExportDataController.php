<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\Exporters\AuditLogExporter;
use Reconmap\Repositories\Exporters\ClientsExporter;
use Reconmap\Repositories\Exporters\CommandsExporter;
use Reconmap\Repositories\Exporters\DocumentsExporter;
use Reconmap\Repositories\Exporters\Exportable;
use Reconmap\Repositories\Exporters\ProjectsExporter;
use Reconmap\Repositories\Exporters\TasksExporter;
use Reconmap\Repositories\Exporters\UsersExporter;
use Reconmap\Repositories\Exporters\VulnerabilitiesExporter;
use Reconmap\Repositories\Exporters\VulnerabilityTemplatesExporter;
use Reconmap\Services\AuditLogService;

class ExportDataController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $entities = explode(',', $request->getQueryParams()['entities']);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $entities);

        $fileName = 'reconmap-data-' . date('Ymd-His') . '.json';

        $exportables = [
            'auditlog' => AuditLogExporter::class,
            'clients' => ClientsExporter::class,
            'commands' => CommandsExporter::class,
            'documents' => DocumentsExporter::class,
            'projects' => ProjectsExporter::class,
            'project_templates' => ProjectsExporter::class,
            'tasks' => TasksExporter::class,
            'users' => UsersExporter::class,
            'vulnerabilities' => VulnerabilitiesExporter::class,
            'vulnerability_templates' => VulnerabilityTemplatesExporter::class,
        ];

        $body = new CallbackStream(function () use ($exportables, $entities) {
            $data = [];

            $outputStream = fopen('php://output', 'w');

            foreach ($exportables as $exportableKey => $exportable) {
                if (in_array($exportableKey, $entities)) {
                    /** @var Exportable $exporter */
                    $exporter = $this->container->get($exportable);
                    $data[$exportableKey] = $exporter->export($exportableKey);
                }
            }

            fwrite($outputStream, json_encode($data));
        });

        $response = new Response;
        return $response
            ->withBody($body)
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
            ->withAddedHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    private function auditAction(int $loggedInUserId, array $entities): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::DATA_EXPORTED, $entities);
    }
}
