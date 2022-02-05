<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\Exporters\Exportable;
use Reconmap\Repositories\Exporters\Exportables;
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

        $packageName = match (count($entities)) {
            1 => $entities[0],
            default => 'data',
        };

        $fileName = 'reconmap-' . $packageName . '-' . date('Ymd-His') . '.json';

        $body = new CallbackStream(function () use ($entities) {
            $data = [];

            $outputStream = fopen('php://output', 'w');

            foreach (Exportables::List as $exportable) {
                $exportableKey = $exportable['key'];
                if (in_array($exportableKey, $entities)) {
                    /** @var Exportable $exporter */
                    $exporter = $this->container->get($exportable['className']);
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
