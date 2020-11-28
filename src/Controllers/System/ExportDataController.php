<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
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

        $xml = new \DomDocument('1.0', 'UTF-8');

        $body = new CallbackStream(function () use ($xml, $entities) {
            $f = fopen('php://output', 'w');

            $rootNode = $xml->createElement('reconmap');

            if (in_array('vulnerabilities', $entities)) {
                $vulnerabilityRepository = new VulnerabilityRepository($this->db);
                $vulnerabilities = $vulnerabilityRepository->findAll();
                $vulnerabilitiesNode = $xml->createElement('vulnerabilities');
                foreach ($vulnerabilities as $vulnerability) {
                    $vulnerabilityNode = $xml->createElement('vulnerability');
                    $vulnerabilityNode->setAttribute('summary', $vulnerability['summary']);

                    $vulnerabilityDescriptionNode = $xml->createElement('description');
                    if (isset($vulnerability['description'])) {
                        $vulnerabilityDescriptionNode->appendChild($xml->createTextNode($vulnerability['description']));
                    }
                    $vulnerabilityNode->appendChild($vulnerabilityDescriptionNode);

                    $vulnerabilitiesNode->appendChild($vulnerabilityNode);
                }
                $rootNode->appendChild($vulnerabilitiesNode);
            }

            $xml->appendChild($rootNode);

            $xml->formatOutput = true;
            fwrite($f, $xml->saveXML());
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

