<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use DomDocument;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\Converters\VulnerabilityXmlConverter;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\AuditLogService;

class ExportDataController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $entities = explode(',', $request->getQueryParams()['entities']);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $entities);

        $fileName = 'reconmap-data-' . date('Ymd-His') . '.xml';

        $xmlDoc = new DomDocument('1.0', 'UTF-8');

        $body = new CallbackStream(function () use ($xmlDoc, $entities) {
            $f = fopen('php://output', 'w');

            $rootNode = $xmlDoc->createElement('reconmap');

            if (in_array('vulnerabilities', $entities)) {
                $vulnerabilityConverter = new VulnerabilityXmlConverter();
                $vulnerabilityRepository = new VulnerabilityRepository($this->db);
                $vulnerabilities = $vulnerabilityRepository->findAll();
                $vulnerabilitiesNode = $xmlDoc->createElement('vulnerabilities');
                foreach ($vulnerabilities as $vulnerability) {
                    $vulnerabilityEl = $vulnerabilityConverter->toXml($xmlDoc, $vulnerability);
                    $vulnerabilitiesNode->appendChild($vulnerabilityEl);
                }
                $rootNode->appendChild($vulnerabilitiesNode);
            }

            $xmlDoc->appendChild($rootNode);

            $xmlDoc->formatOutput = true;
            fwrite($f, $xmlDoc->saveXML());
        });

        $response = new Response;
        return $response
            ->withBody($body)
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
            ->withAddedHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    private function auditAction(int $loggedInUserId, array $entities): void
    {
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($loggedInUserId, AuditLogAction::DATA_EXPORTED, $entities);
    }
}

