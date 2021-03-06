<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\Exporters\ClientsExporter;
use Reconmap\Repositories\Exporters\CommandsExporter;
use Reconmap\Repositories\Exporters\DocumentsExporter;
use Reconmap\Repositories\Exporters\ProjectsExporter;
use Reconmap\Repositories\Exporters\TasksExporter;
use Reconmap\Repositories\Exporters\UsersExporter;
use Reconmap\Repositories\Exporters\VulnerabilitiesExporter;
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
            'clients' => ClientsExporter::class,
            'commands' => CommandsExporter::class,
            'documents' => DocumentsExporter::class,
            'projects' => ProjectsExporter::class,
            'tasks' => TasksExporter::class,
            'users' => UsersExporter::class,
            'vulnerabilities' => VulnerabilitiesExporter::class
        ];

        $body = new CallbackStream(function () use ($exportables, $entities) {
            $data = [];

            $outputStream = fopen('php://output', 'w');

            foreach ($exportables as $exportableKey => $exportable) {
                if (in_array($exportableKey, $entities)) {
                    $exporter = $this->container->get($exportable);
                    $data[$exportableKey] = $exporter->export();
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
