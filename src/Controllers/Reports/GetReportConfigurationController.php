<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\ReportConfigurationRepository;

class GetReportConfigurationController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): object
    {
        $projectId = (int)$args['projectId'];

        $repository = new ReportConfigurationRepository($this->db);
        $config = $repository->findByProjectId($projectId);
        if (is_null($config)) {
            $config = new ReportConfiguration();
        }

        return $config;
    }
}
