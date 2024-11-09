<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\Exporters\Exportable;
use Reconmap\Repositories\Exporters\Exportables;
use Reconmap\Services\AuditLogService;

class ExportDataController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $entitiesParam = explode(',', $request->getQueryParams()['entities']);
        $entities = array_filter($entitiesParam, fn($entity) => in_array($entity, array_column(Exportables::List, 'key')));
        $invalidEntities = array_diff($entitiesParam, $entities);

        if (!empty($invalidEntities)) {
            $this->logger->warning("Trying to export invalid entities", $invalidEntities);

            return $this->createBadRequestResponse();
        }

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $entities);

        $packageName = match (count($entities)) {
            1 => $entities[0],
            default => 'data',
        };

        $fileName = 'reconmap-' . $packageName . '-' . date('Ymd-His') . '.json';

        $data = [];

        foreach (Exportables::List as $exportable) {
            $exportableKey = $exportable['key'];
            if (in_array($exportableKey, $entities)) {
                /** @var Exportable $exporter */
                $exporter = $this->container->get($exportable['className']);
                $data[$exportableKey] = $exporter->export();
            }
        }

        $body = json_encode($data);

        $response = new Response;
        return $response
            ->withBody(Utils::streamFor($body))
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
            ->withAddedHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    private function auditAction(int $loggedInUserId, array $entities): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::DATA_EXPORTED, $entities);
    }
}
