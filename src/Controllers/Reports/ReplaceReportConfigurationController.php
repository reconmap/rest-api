<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\ReportConfigurationRepository;

class ReplaceReportConfigurationController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        /** @var ReportConfiguration $reportConfiguration */
        $reportConfiguration = $this->getJsonBodyDecoded($request);
        $reportConfiguration->project_id = $projectId;

        $repository = new ReportConfigurationRepository($this->db);
        $result = $repository->insert($reportConfiguration);

        return ['success' => $result];
    }
}
