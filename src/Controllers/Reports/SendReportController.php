<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\RedisServer;

class SendReportController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $reportId = (int)$args['reportId'];

        $repository = new ReportRepository($this->db);
        $report = $repository->findById($reportId);

        $filename = sprintf(RECONMAP_APP_DIR . "/data/reports/report-%d.%s", $reportId, $report['format']);

        $deliverySettings = $this->getJsonBodyDecoded($request);

        $emailBody = $deliverySettings->body;
        $recipients = explode(',', $deliverySettings->recipients);
        $this->logger->debug('recipients', [$recipients]);

        /** @var RedisServer $redis */
        $redis = $this->container->get(RedisServer::class);
        $result = $redis->lPush("email:queue",
            json_encode([
                'subject' => $deliverySettings->subject,
                'to' => $recipients,
                'body' => $emailBody,
                'attachmentPath' => $filename
            ])
        );
        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'email:queue']);
        }

        return ['success' => true];
    }
}
